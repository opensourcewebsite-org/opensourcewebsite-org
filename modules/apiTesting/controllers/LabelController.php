<?php

namespace app\modules\apiTesting\controllers;

use app\modules\apiTesting\models\ApiTestLabel;
use app\modules\apiTesting\models\ApiTestLabelController;
use app\modules\apiTesting\models\ApiTestProject;
use app\modules\apiTesting\models\ApiTestServer;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * LabelController implements the CRUD actions for ApiTestLabel model.
 */
class LabelController extends Controller
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
     * Lists all ApiTestLabel models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $project = $this->findProject($id);
        $searchModel = new ApiTestLabelController();
        $dataProvider = $searchModel->search([]);
        $dataProvider->query->andWhere(['project_id' => $project->id]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'project' => $project
        ]);
    }

    /**
     * Displays a single ApiTestLabel model.
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
     * Creates a new ApiTestLabel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $project = $this->findProject($id);

        $model = new ApiTestLabel([
            'project_id' => $project->id
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $project->id]);
        }

        return $this->renderAjax('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ApiTestLabel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->project_id]);
        }

        return $this->renderAjax('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ApiTestLabel model.
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
     * Finds the ApiTestLabel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ApiTestLabel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ApiTestLabel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function findProject($id)
    {
        $project = ApiTestProject::find()->my()->andWhere(['id' => $id])->one();
        if ( ! $project) {
            throw new NotFoundHttpException();
        }
        return $project;
    }
}
