<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\AdKeyword;
use app\models\JobKeyword;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class AdKeywordController extends Controller
{
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create-ajax' => ['POST'],
                ],
            ],
        ];
    }

    public function actionCreateAjax(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AdKeyword();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return ['success' => true, 'id' => $model->id, 'keyword' => $model->keyword];
        }

        return ['success' => false];
    }
}
