<?php

namespace app\controllers;

use Yii;
use app\components\Controller;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\User;

class UsersController extends Controller
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
     * Lists all User models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $query = User::find()
            ->where([
                'status' => User::STATUS_ACTIVE,
            ])
            ->orderBy([
                'rating' => SORT_DESC,
                'created_at' => SORT_ASC,
            ]);

        $usersCount = $query->count();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'usersCount' => $usersCount,
            'dataProvider' => $dataProvider,
        ]);
    }
}
