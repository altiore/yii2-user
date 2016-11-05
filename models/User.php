<?php

namespace altiore\user\models;

use frontend\models\PasswordResetToken;
use Yii;
use yii\authclient\ClientInterface;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use common\components\JWT;

/**
 * User model
 *
 * @property integer      $id
 * @property string       $username
 * @property string       $first_name
 * @property string       $last_name
 * @property string       $password_hash
 * @property string       $password_reset_token
 * @property string       $email
 * @property string       $auth_key
 * @property integer      $status
 * @property integer      $created_at
 * @property integer      $updated_at
 * @property string       $password write-only password
 * @property integer      $role_id
 * @property UserSocial[] $userSocials
 * @property Role         $role
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * @var array EAuth attributes
     */
    public $profile;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['auth_key', 'password_hash', 'email'], 'required'],
            [['status', 'role_id'], 'integer'],
            [['username', 'password_hash', 'email', 'first_name', 'last_name'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['email'], 'email'],
            [['email'], 'unique'],
            [
                ['role_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Role::class,
                'targetAttribute' => ['role_id' => 'id'],
            ],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'username',
            'email',
        ];
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if ($this->isNewRecord && empty($this->role_id)) {
            $this->role_id = Role::ROLE_GUEST;
        }

        return parent::beforeValidate();
    }

    public function getProfile()
    {
        return $this->hasOne(Profile::class, ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::findOne(['auth_key' => JWT::getAuthKeyByToken($token)]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email
     *
     * @param $email
     * @return null|static
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status'               => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];

        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->id;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $passwordResetToken = new PasswordResetToken();
        $passwordResetToken->user_id = $this->id;
        $passwordResetToken->token = Yii::$app->security->generateRandomString() . '_' . time();
        $passwordResetToken->save();

        return $passwordResetToken->token;
    }

    /**
     * @return bool
     */
    public function activate()
    {
        $this->status = User::STATUS_ACTIVE;

        return $this->updateAttributes(['status']);
    }

    /**
     * @param ClientInterface $client
     * @return bool|$this
     */
    public static function createByClientInfo(ClientInterface $client)
    {
        $clientAttributes = $client->getUserAttributes();

        $social_user_id = ArrayHelper::getValue($clientAttributes, 'id');

        if ($social_user_id !== null) {
            $password = Yii::$app->security->generateRandomString(6);
            $user = new static([
                'email'      => ArrayHelper::getValue($clientAttributes, 'email'),
                'first_name' => ArrayHelper::getValue($clientAttributes, 'first_name'),
                'last_name'  => ArrayHelper::getValue($clientAttributes, 'last_name'),
                'password'   => $password,
                'status'     => 10,
            ]);
            $user->generateAuthKey();

            $transaction = Yii::$app->db->beginTransaction();

            if ($user->save()) {
                if (UserSocial::createByClientInfo($client, $user->id) !== null) {
                    $transaction->commit();
                } else {
                    $user->addError('status', 'Cant Save OAuth Info');
                }
            }

            return $user;
        }

        return null;
    }
}
