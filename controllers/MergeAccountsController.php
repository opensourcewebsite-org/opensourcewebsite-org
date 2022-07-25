<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\base\Exception;

use app\services\MergeAccountsService;
use app\models\forms\MergeAccountsForm;

class MergeAccountsController extends Controller
{
    public MergeAccountsService $mergeAccountsService;

    function __construct()
    {
        parent::__construct(...func_get_args());

        $this->mergeAccountsService = new MergeAccountsService();
    }

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

    public function actionIndex()
    {
        $model = new MergeAccountsForm();

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            if ($model->load($postData) && $model->login()) {
                if ($this->mergeAccountsService->mergeAccounts($this->user, $model->user)) {
                    return $this->render('done', [
                        'user' => $this->user,
                    ]);
                }
            }
        }

        return $this->render('index', [
            'model' => $model,
            'user' => $this->user,
        ]);
    }
}
