<?php

namespace altiore\user\models;

use Yii;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "profile".
 *
 * @property integer $user_id
 * @property string  $sex
 * @property string  $family_status
 * @property string  $language
 * @property string  $country
 * @property string  $city
 * @property string  $phone
 * @property string  $skype
 * @property string  $interests
 * @property string  $about_me
 * @property string  $smoking
 * @property string  $alcohol
 * @property string  $source_inspiration
 *
 * @property User    $user
 */
class Profile extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%profile}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'safe'],
            [['interests', 'about_me'], 'string'],
            [['sex', 'family_status'], 'string', 'max' => 20],
            [['language', 'country', 'city', 'phone', 'skype', 'source_inspiration'], 'string', 'max' => 255],
            [['smoking', 'alcohol'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id'            => Yii::t('app', 'User ID'),
            'sex'                => Yii::t('app', 'Sex'),
            'family_status'      => Yii::t('app', 'Family Status'),
            'language'           => Yii::t('app', 'Language'),
            'country'            => Yii::t('app', 'Country'),
            'city'               => Yii::t('app', 'City'),
            'phone'              => Yii::t('app', 'Phone'),
            'skype'              => Yii::t('app', 'Skype'),
            'interests'          => Yii::t('app', 'Interests'),
            'about_me'           => Yii::t('app', 'About Me'),
            'smoking'            => Yii::t('app', 'Smoking'),
            'alcohol'            => Yii::t('app', 'Alcohol'),
            'source_inspiration' => Yii::t('app', 'Source Inspiration'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Yii::$app->getUser()->identityClass, ['id' => 'user_id']);
    }
}
