<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\AdSearch;
use app\models\search\AdOfferSearch;
use yii\filters\AccessControl;
use yii\web\Controller;

class AdSearchController extends Controller {

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
        $searchModel = new AdOfferSearch(['status' => AdSearch::STATUS_ON]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
}
