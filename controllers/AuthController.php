<?php

namespace altiore\user\controllers;

use Yii;
use yii\authclient\AuthAction;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;
use yii\web\MethodNotAllowedHttpException;
use yii\web\ServerErrorHttpException;
use common\components\RestController;
use altiore\user\models\User;
use altiore\user\models\SignupForm;
use altiore\user\models\LoginForm;
use altiore\user\models\UserSocial;

/**
 * Class AuthController
 * @package api\controllers
 */
class AuthController extends RestController
{
    public $authExceptActions = [
        'signup',
        'login',
        'oauth',
        'test',
    ];

    /**
     * @return array
     */
    public function verbs()
    {
        return [
            'signup' => ['POST'],
            'login'  => ['POST'],
            'oauth'  => ['GET'],
            'test'   => ['GET'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'oauth' => [
                'class'           => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    /**
     * @return \yii\web\Response
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post(), '')) {
            if ($user = $model->signup()) {
                Yii::$app->getResponse()->setStatusCode(201);
                if (Yii::$app->getUser()->login($user)) {
                    return $user;
                }
            }
        }

        return $model;
    }

    /**
     * @return LoginForm|User
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->getRequest()->post(), '')) {
            if ($model->login()) {
                return Yii::$app->getUser()->getIdentity();
            }
        }

        return $model;
    }

    /**
     * @return string
     */
    public function actionTest()
    {
        return $this->renderPartial('test');
    }

    /**
     * @param ClientInterface $client
     * @return string
     * @throws MethodNotAllowedHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function onAuthSuccess(ClientInterface $client)
    {
        if (!Yii::$app->getUser()->getIsGuest()) {
            throw new MethodNotAllowedHttpException();
        }

        /** @var UserSocial $auth */
        $auth = UserSocial::findByClientInfo($client);

        if ($auth === null) {
            // try to find exists user
            $user = User::findOne([
                'email' => ArrayHelper::getValue($client->getUserAttributes(), 'email'),
            ]);
            if ($user !== null) { // touch exists user to Client
                if (($auth = UserSocial::createByClientInfo($client, $user->id)) === null) {
                    return $this->errorResponse();
                }
            } else { // create new user from client info
                $user = User::createByClientInfo($client);
            }
        } else { // exists user
            $user = $auth->user;
        }

        if ($user === null) {
            return $this->errorResponse();
        }

        return \Yii::createObject([
            'class'   => 'yii\web\Response',
            'content' => $this->render('safe-page', ['data' => $user]),
        ]);
    }

    protected function errorResponse()
    {
        return false;
    }
}
