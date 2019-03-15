<?php

namespace app\controllers;

use Yii;
use app\models\WikinewsPage;
use app\models\WikinewsPageSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\WikinewsLanguage;

/**
 * WikinewsPageController implements the CRUD actions for WikinewsPage model.
 */
class WikinewsPagesController extends Controller
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all WikinewsPage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WikinewsPageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $model = new WikinewsPage();
        
        $language_arr= ArrayHelper::map(WikinewsLanguage::find()->all(),'id','name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'language_arr' => $language_arr,
        ]);
    }

    /**
     * Displays a single WikinewsPage model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new WikinewsPage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new WikinewsPage();
        $language_arr= ArrayHelper::map(WikinewsLanguage::find()->all(),'id','name');
        $post_data=Yii::$app->request->post();
        if ($model->load($post_data) && $model->validate()) {
            $alreadyAvailableUrl=WikinewsPage::find()->where(['wikinews_page_url'=>$model->wikinews_page_url])->one();
            if(!empty($alreadyAvailableUrl)){
                $alreadyAvailableUrl->parsed_at=null;
                $alreadyAvailableUrl->save(false);
            }else{
                $model->save(false);
            }
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'language_arr' => $language_arr,
        ]);
    }

    /**
     * Updates an existing WikinewsPage model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdatex($id)
    {
        $model = $this->findModel($id);
        $language_arr= ArrayHelper::map(WikinewsLanguage::find()->all(),'id','name');
        $post_data=Yii::$app->request->post();
        if ($model->load($post_data) && $model->validate()) {

            $alreadyAvailableUrl=WikinewsPage::find()->where(['wikinews_page_url'=>$model->wikinews_page_url])->andWhere(['<>','id', $model->id])->one();
            if(!empty($alreadyAvailableUrl)){
                $alreadyAvailableUrl->parsed_at=null;
                $alreadyAvailableUrl->save(false);
            }else{
                $model->save(false);
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'language_arr' => $language_arr,
        ]);
    }

    /**
     * Deletes an existing WikinewsPage model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the WikinewsPage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WikinewsPage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WikinewsPage::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
