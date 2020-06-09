<?php

namespace app\controllers;

use app\models\Country;
use app\models\Currency;
use app\models\Language;
use app\models\PaymentMethod;
use app\models\Gender;
use app\models\Sexuality;
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
                'only' => ['country', 'currency', 'language', 'payment-method', 'gender', 'sexuality'],
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

    public function actionPaymentMethod()
    {
        $paymentMethod = PaymentMethod::find();
        $countQuery = clone $paymentMethod;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $paymentMethod->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('payment-method', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    public function actionPaymentMethodShow($id)
    {
        $paymentMethod = PaymentMethod::findOne($id);
        $currency = $paymentMethod->getCurrency();
        $countQuery = clone $currency;
        $pages =  new Pagination(['totalCount' => $countQuery->count()]);
        $models = $currency->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('payment-method-show', [
            'models' => $models,
            'pages' => $pages,
            'paymentMethod' => $paymentMethod,
        ]);
    }

    public function actionGender()
    {
        $gender = Gender::find();
        $countQuery = clone $gender;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $gender->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('gender', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    public function actionSexuality()
    {
        $sexuality = Sexuality::find();
        $countQuery = clone $sexuality;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $sexuality->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('sexuality', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }
}
