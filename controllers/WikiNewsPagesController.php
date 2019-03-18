<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\WikiLanguage;
use app\models\WikinewsPage;
use app\models\UserWikiToken;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\models\WikiNewsPageSearch;

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
                        'actions' => ['index', 'view', 'missing','create'],
                        'roles' => ['@'],
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($viewYours = false)
    {
		
		$params = Yii::$app->request->queryParams;
        $searchModel = new WikiNewsPageSearch();
		
        $dataProvider = $searchModel->search($params);
         $dataProvider->pagination->pageSize=5;
        if ($viewYours) {
            $params['viewYours'] = true;
        }

         return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
			
           
            
        ]);

       
    }

	/**
     * @param $post
     *
     * @return string
     * @throws NotFoundHttpException
     */
	public function actionCreate()
    {		
        $model = new WikinewsPage();
        $languageArray = WikiLanguage::find()->all();
        if (Yii::$app->request->isGet && Yii::$app->request->isAjax) {
			
			$postvar =Yii::$app->request->get() ;
			if(!empty($postvar)){
				$postvar['WikinewsPage']['created_at'] = strtotime(date('Y-m-d h:i:s'));
				$postvar['WikinewsPage']['pageid'] = 1	;		
			}
		
            if ($model->load($postvar) && $model->save()) {
                return $this->redirect(['wikinews-pages/index']);
            }
        }
        
        return $this->renderAjax('form', [
            'model' => $model,
            'languageArray' => $languageArray,
        ]);
    }
    /**
     * @param $code
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($code, $all = false)
    {
        if (!$language = WikiLanguage::findOne(['code' => $code])) {
            throw new NotFoundHttpException();
        }

        Url::remember();

        $searchModel = new WikiPageSearch(['language_id' => $language->id]);
        $dataProvider = $searchModel->search(array_merge(
            Yii::$app->request->queryParams,
            ['allUsers' => $all]
        ));

        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => Yii::t('app', $all ? 'All users pages' : 'Your pages') . " ({$language->code}.wikipedia.org)",
        ]);
    }

    /**
     * @param $code
     *
     * @return WikiLanguage
     * @throws NotFoundHttpException
     */
    protected function findLanguage($code)
    {
        if (!$language = WikiLanguage::findOne(['code' => $code])) {
            throw new NotFoundHttpException();
        }

        return $language;
    }

    /**
     * List the missing pages
     */
    public function actionMissing($userId, $languageId)
    {
        $language = WikiLanguage::findOne($languageId);
        $params = [];

        if (isset(Yii::$app->request->queryParams['WikiPageSearch'])) {
            $params = Yii::$app->request->queryParams['WikiPageSearch'];
        }

        $searchModel = new WikiPageSearch($params);
        $dataProvider = $searchModel->searchMissing([
            'userId' => $userId,
            'languageId' => $languageId,
        ]);

        return $this->render('missing', [
            'language' => $language,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
