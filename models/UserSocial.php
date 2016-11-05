<?php

namespace altiore\user\models;

use Yii;
use yii\authclient\ClientInterface;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "auth".
 * @property integer $id
 * @property integer $user_id
 * @property string  $client_id
 * @property string  $client_user_id
 * @property string  $client_user_profile_url
 * @property integer $created_at
 * @property integer $updated_at
 * @property User    $user
 */
class UserSocial extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_socials}}';
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'user_id'], 'safe'],
            [['client_id', 'client_user_id'], 'required'],
            [['client_id', 'client_user_id', 'client_user_profile_url'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                      => 'ID',
            'user_id'                 => 'User ID',
            'client_id'               => 'Client ID',
            'client_user_id'          => 'Client User ID',
            'client_user_profile_url' => 'Client User Profile Url',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            //            [
            //                'class'              => BlameableBehavior::class,
            //                'createdByAttribute' => 'user_id',
            //                'updatedByAttribute' => false,
            //            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param ClientInterface $client
     * @return null|static
     */
    public static function findByClientInfo(ClientInterface $client)
    {
        $id = ArrayHelper::getValue($client->getUserAttributes(), 'id');
        if ($id === null || !is_string($id)) {
            return null;
        }

        return static::findOne([
            'client_id'      => $client->getId(),
            'client_user_id' => $id,
        ]);
    }

    /**
     * @param ClientInterface $client
     * @param  integer|null   $user_id
     * @return static
     */
    public static function createByClientInfo(ClientInterface $client, $user_id = null)
    {
        $clientAttributes = $client->getUserAttributes();

        $auth = new static([
            'user_id'                 => $user_id,
            'client_id'               => $client->getId(),
            'client_user_id'          => ArrayHelper::getValue($clientAttributes, 'id'),
            'client_user_email'       => ArrayHelper::getValue($clientAttributes, 'email'),
            'client_user_profile_url' => ArrayHelper::getValue($clientAttributes, 'public-profile-url'),
        ]);

        if ($auth->save()) {
            return $auth;
        }

        return null;
    }
}
