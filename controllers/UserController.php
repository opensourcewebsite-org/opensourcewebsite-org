<?php

namespace app\controllers;

use Yii;
use app\models\User;
use yii\web\Controller;
use yii\filters\AccessControl;

class UserController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['display'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionDisplay()
    {
        $confirmed_users = User::findAll(['is_email_confirmed' => true]);

        return $this->render('display', [
            'confirmed_users' => count($confirmed_users),
        ]);
    }
}
