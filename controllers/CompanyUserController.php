<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\Company;
use app\models\CompanyUser;
use app\models\search\CompanyUserSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CompanyUserController extends Controller {

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

    public function actionCreate(): string
    {

    }

    public function actionUpdate(): string
    {

    }

    public function actionIndex(): string
    {
        $searchModel = new CompanyUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

}
