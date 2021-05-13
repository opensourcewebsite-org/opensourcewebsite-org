<?php
declare(strict_types=1);

namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;

class VacancyController extends Controller {
    public function behaviors(): array
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

    public function actionIndex(): string
    {

        return $this->render('index');
    }
}
