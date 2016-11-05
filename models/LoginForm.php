<?php

namespace altiore\user\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $email;
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'username'], 'filter', 'filter' => 'trim'],
            ['password', 'required'],
            ['password', 'validatePassword'],

            ['email', 'required', 'when' => function ($model) {
                return empty($model->username);
            }],
            ['email', 'email'],

            ['username', 'required', 'when' => function ($model) {
                return empty($model->email);
            }],
            ['username', 'string', 'min' => 3, 'max' => 255],

            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]] or [[email]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            if (empty($this->email)) {
                $this->_user = User::findByUsername($this->username);
            } else {
                $this->_user = User::findByEmail($this->email);
            }
        }

        return $this->_user;
    }
}
