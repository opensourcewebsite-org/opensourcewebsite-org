<?php


namespace app\controllers;


use yii\filters\AccessControl;
use yii\web\Controller;

class ReferralsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {

        return $this->render('index');
    }
}