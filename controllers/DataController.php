<?php
namespace app\controllers;

use app\models\Country;
use app\models\Currency;
use app\models\Language;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;

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
                'only' => ['country', 'currency', 'language'],
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

    public function actionCurrency()
    {
        $currency = Currency::find();
        $countQuery = clone $currency;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $currency->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('currency', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    public function actionLanguage()
    {
        $language = Language::find();
        $countQuery = clone $language;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $language->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('language', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }
}
