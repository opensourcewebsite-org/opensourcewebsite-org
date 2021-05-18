<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\search\VacancySearch;
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

    public function actionCreate()
    {

    }

    public function actionUpdate()
    {

    }

    public function actionView()
    {

    }

    public function actionIndex(): string
    {

        $searchModel = new VacancySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }
}
