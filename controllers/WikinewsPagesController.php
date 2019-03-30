<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\WikiLanguage;
use app\models\UserWikiToken;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\models\search\WikinewsSearch;
use app\models\WikinewsPage;
use app\models\WikinewsLanguage;

/**
 * WikipediaPagesController implements the CRUD actions for WikiPage model.
 */
class WikinewsPagesController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'create'],
                        'roles' => ['@'],
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        Url::remember();

        $searchModel = new WikinewsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }
	
	public function actionCreate()
    {
        $model = new WikinewsPage([
            'created_by' => Yii::$app->user->id,
            'created_at' => time()
        ]);

        $languageArray = WikinewsLanguage::find()->all();

        if (Yii::$app->request->isGet && Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->get()) && $model->validate()) {				
				preg_match("#https://([a-z]{2}).wikinews.org/wiki/([A-Za-z0-9,_.-]+)$#", $model->url, $matches);
				if (isset($matches[1])) {
					$model->language_id = WikinewsLanguage::find()->select('id')->where(['code'=>$matches[1]])->scalar();
				}
				if (isset($matches[2])) {
					$model->title = trim($matches[2]);
				}
				$wikiNewsPage = WikinewsPage::find()->where(['language_id'=>$model->language_id, 'title'=>$model->title])->one();
				if ($wikiNewsPage) {
					$wikiNewsPage->parsed_at = NULL;
					$wikiNewsPage->url = $model->url;
					$model = $wikiNewsPage;
				}
				if($model->save()){
					return $this->redirect(['wikinews-pages/index']);
				}                
            }
        }
        
        return $this->renderAjax('form', [
            'model' => $model,
            'languageArray' => $languageArray,
        ]);
    }
}
