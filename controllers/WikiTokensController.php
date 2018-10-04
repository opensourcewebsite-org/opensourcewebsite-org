<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use app\models\UserWikiToken;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class WikiTokensController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionCreate($language_id = null)
    {
        $model = new UserWikiToken([
            'user_id' => Yii::$app->user->id,
            'language_id' => $language_id,
        ]);

        if (Yii::$app->request->isGet && Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->get()) && $model->save()) {
                return $this->redirect(['wikipedia-pages/index']);
            }

            return $this->renderAjax('form', ['model' => $model]);
        }

        return $this->goBack();
    }

    public function actionUpdate($id)
    {
        $model = UserWikiToken::findOne($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if (Yii::$app->request->isGet && Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->get()) && $model->save()) {
                return $this->redirect(['wikipedia-pages/index']);
            }

            return $this->renderAjax('form', ['model' => $model]);
        }

        return $this->goBack();
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->goBack();
    }

    /**
     * @param $id
     * @return UserWikiToken
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (!$model = UserWikiToken::findOne($id)) {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}
