<?php

namespace app\controllers;

use app\components\Controller;
use app\components\Converter;
use app\models\Contact;
use app\models\Country;
use app\models\Currency;
use app\models\EditProfileForm;
use app\models\Gender;
use app\models\Language;
use app\models\LanguageLevel;
use app\models\Sexuality;
use app\models\User;
use app\models\UserCitizenship;
use app\models\UserEmail;
use app\models\UserLanguage;
use app\models\UserStatistic;
use Yii;
use yii\data\Pagination;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class StatisticsController extends Controller
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

    /**
     *
     * @param string $type
     * @return mixed
     */
    public function actionIndex($type = 'age')
    {
        $usersCount = User::find()->count();

        $userStatistics = new UserStatistic();
        $dataProvider = $userStatistics->getDataProvider($type);

        return $this->render('index', [
            'usersCount' => $usersCount,
            'dataProvider' => $dataProvider,
        ]);
    }
}
