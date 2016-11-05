<?php

namespace altiore\user\controllers;

use common\components\ActiveController;
use altiore\user\models\Profile;

class ProfileController extends ActiveController
{
    public $modelClass = Profile::class;

}
