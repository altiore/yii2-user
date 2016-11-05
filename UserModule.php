<?php

namespace altiore\user;

/**
 * user module definition class
 */
class UserModule extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'altiore\user\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
}
