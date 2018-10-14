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
use app\models\search\WikiPageSearch;

/**
 * WikipediaPagesController implements the CRUD actions for WikiPage model.
 */
class WikipediaPagesController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'roles' => ['@'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->is_email_confirmed;
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        Url::remember();

        $searchModel = new WikiPageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $tokensDataProvider = new ActiveDataProvider([
            'query' => UserWikiToken::find()
                ->with('language')
                ->where(['user_id' => Yii::$app->user->id]),
            'sort' => false,
            'pagination' => false,
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'tokensDataProvider' => $tokensDataProvider,
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
}
