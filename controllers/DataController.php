<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Country;
use yii\data\Pagination;
use yii\filters\AccessControl;

class DataController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['country'],
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

    public function actionCountrya()
    {
        $country = Country::find()
            ->all();
        
        return $this->render('country', [
            'country' => $country,
        ]);
    }
    
    public function actionCountry()
    {
        $country = Country::find();
        $countQuery = clone $country;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $country->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('country', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }
}
