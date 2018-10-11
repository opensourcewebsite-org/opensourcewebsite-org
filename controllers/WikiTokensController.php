<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use app\models\UserWikiToken;
use app\models\WikiLanguage;
use app\models\UserWikiPage;
use app\models\WikiPage;
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

    public function actionCreate()
    {
        $model = new UserWikiToken([
            'user_id' => Yii::$app->user->id
        ]);

        $languageArray = WikiLanguage::find()->where([
            'not in', 'id',
            UserWikiToken::find()->select('id')->where(['user_id' => Yii::$app->user->id]),
        ])->all();

        if (Yii::$app->request->isGet && Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->get()) && $model->save()) {
                return $this->redirect(['wikipedia-pages/index']);
            }
        }
        
        return $this->renderAjax('form', [
            'model' => $model,
            'languageArray' => $languageArray,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = UserWikiToken::findOne($id);

        if (Yii::$app->request->isGet && Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->get()) && $model->save()) {
                return $this->redirect(['wikipedia-pages/index']);
            }
        }
        
        return $this->renderAjax('form', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $pageIds = $model->wikiPagesIds;

        if (!empty($pageIds)) {
            UserWikiPage::deleteAll([
                'user_id' => Yii::$app->user->id,
                'wiki_page_id' => $pageIds,
            ]);

            WikiPage::deleteAll([
                'id' => $pageIds,
            ]);
        }

        $model->delete();

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
