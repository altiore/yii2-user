<?php

namespace altiore\user\controllers;

use Yii;
use altiore\user\models\User;
use common\components\ActiveController;

class DefaultController extends ActiveController
{
    /** @var string */
    public $modelClass = User::class;
}
