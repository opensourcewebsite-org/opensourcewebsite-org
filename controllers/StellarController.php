<?php

namespace app\controllers;

use Yii;
use app\components\Controller;
use yii\filters\AccessControl;

class StellarController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
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

    public function actionDepositIncome()
    {
        return $this->render('deposit-income');
    }

    public function actionFortuneGame()
    {
        return $this->render('fortune-game');
    }
}
