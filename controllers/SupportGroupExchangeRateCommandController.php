<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\base\ViewContextInterface;
use app\models\SupportGroupExchangeRateCommand;

/**
 * SupportGroupExchangeRateCommandController implements the CRUD actions for SupportGroupExchangeRateCommand model.
 */
class SupportGroupExchangeRateCommandController extends Controller implements ViewContextInterface
{

    /**
     * {@inheritdoc}
     */
    public function getViewPath()
    {
        return Yii::getAlias('@app/views/support-groups/exchange-rate-commands');
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all SupportGroupExchangeRateCommand models.
     * @return mixed
     */
    public function actionIndex($supportGroupExchangeRateId, $type)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SupportGroupExchangeRateCommand::find()
                ->andWhere([
                    'support_group_exchange_rate_id' => $supportGroupExchangeRateId,
                    'type' => $type,
                ]),
        ]);

        return $this->render('index', [
            'type' => $type,
            'dataProvider' => $dataProvider,
            'supportGroupExchangeRateId' => $supportGroupExchangeRateId,
        ]);
    }

    /**
     * Displays a single SupportGroupExchangeRateCommand model.
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
     * Creates a new SupportGroupExchangeRateCommand model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($supportGroupExchangeRateId, $type)
    {
        $model = new SupportGroupExchangeRateCommand();
        $model->type = $type;
        $model->support_group_exchange_rate_id = $supportGroupExchangeRateId;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'supportGroupExchangeRateId' => $supportGroupExchangeRateId, 'type' => $type]);
        }

        return $this->render('create', [
            'type' => $type,
            'model' => $model,
            'supportGroupExchangeRateId' => $supportGroupExchangeRateId,
        ]);
    }

    /**
     * Updates an existing SupportGroupExchangeRateCommand model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $supportGroupExchangeRateId, $type)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'supportGroupExchangeRateId' => $supportGroupExchangeRateId, 'type' => $type]);
        }

        return $this->render('update', [
            'type' => $type,
            'model' => $model,
            'supportGroupExchangeRateId' => $supportGroupExchangeRateId,
        ]);
    }

    /**
     * Deletes an existing SupportGroupExchangeRateCommand model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id, $supportGroupExchangeRateId, $type)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'supportGroupExchangeRateId' => $supportGroupExchangeRateId, 'type' => $type]);
    }

    /**
     * Finds the SupportGroupExchangeRateCommand model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SupportGroupExchangeRateCommand the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupportGroupExchangeRateCommand::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
