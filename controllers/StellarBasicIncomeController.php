<?php

namespace app\controllers;

use Yii;
use app\components\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\UserStellar;
use app\models\Contact;
use app\models\StellarGiver;

class StellarBasicIncomeController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'set-active' => ['POST'],
                    'set-inactive' => ['POST'],
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

    public function actionIndex()
    {
        return $this->render('index', [
            'user' => $this->user,
        ]);
    }

    public function actionSetActive()
    {
        $this->response->format = Response::FORMAT_JSON;

        $this->user->basic_income_on = 1;

        if (!$this->user->save()) {
            return $this->user->getErrors();
        }

        return true;
    }

    public function actionSetInactive()
    {
        $this->response->format = Response::FORMAT_JSON;

        $this->user->basic_income_on = 0;

        if (!$this->user->save()) {
            return $this->user->getErrors();
        }

        return true;
    }

    public function actionParticipant()
    {
        $query = StellarGiver::getParticipantsQuery();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('participant', [
            'usersCount' => $query->count(),
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCandidate()
    {
        $query = User::find()
            ->where([
                'status' => User::STATUS_ACTIVE,
                'basic_income_on' => 1,
                'basic_income_activated_at' => null,
            ])
            ->joinWith('stellar')
            ->andWhere([
                'not',
                [UserStellar::tableName() . '.confirmed_at' => null],
            ])
            ->orderBy([
                'rating' => SORT_DESC,
                'created_at' => SORT_ASC,
            ]);

        $usersCount = $query->count();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('candidate', [
            'usersCount' => $usersCount,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUserView($userId)
    {
        $user = User::find()
            ->where([
                User::tableName() . '.id' => $userId,
                'status' => User::STATUS_ACTIVE,
                'basic_income_on' => 1,
            ])
            ->joinWith('stellar')
            ->andWhere([
                'not',
                [UserStellar::tableName() . '.confirmed_at' => null],
            ])
            ->one();

        if (!$user) {
            return $this->run('stellar-basic-income/candidate');
        }

        $query = User::find()
            ->where([
                'status' => User::STATUS_ACTIVE,
            ])
            ->joinWith('contacts')
            ->andWhere([
                Contact::tableName() . '.link_user_id' => $userId,
                'is_basic_income_candidate' => 1,
            ])
            ->orderBy([
                'rating' => SORT_DESC,
                'created_at' => SORT_ASC,
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('user-view', [
            'user' => $user,
            'dataProvider' => $dataProvider,
        ]);
    }
}
