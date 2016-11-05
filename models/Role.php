<?php

namespace altiore\user\models;

use Yii;

/**
 * This is the model class for table "{{%roles}}".
 * @property integer $id
 * @property string  $name
 * @property string  $permission
 * @property Users[] $users
 */
class Role extends \yii\db\ActiveRecord
{
    const ROLE_GUEST = '0';
    const ROLE_USER  = '1';
    const ROLE_ADMIN = '976';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%role}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['name'], 'string', 'max' => 20],
            [['permission'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'name'       => 'Name',
            'permission' => 'Permission',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['role' => 'id']);
    }
}
