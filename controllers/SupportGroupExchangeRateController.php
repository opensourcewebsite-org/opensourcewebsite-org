<?php

namespace app\controllers;

use app\models\SupportGroupExchangeRate;
use app\repositories\SupportGroupExchangeRateRepository;
use Yii;
use yii\base\ViewContextInterface;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * SupportGroupExchangeRateController implements the CRUD actions for SupportGroupExchangeRate model.
 */
class SupportGroupExchangeRateController extends Controller implements ViewContextInterface
{
    public SupportGroupExchangeRateRepository $supportGroupExchangeRateRepository;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->supportGroupExchangeRateRepository = new SupportGroupExchangeRateRepository();
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPath()
    {
        return Yii::getAlias('@app/views/support-groups/exchange-rate');
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
     * Lists all SupportGroupExchangeRate models.
     * @return mixed
     */
    public function actionIndex($supportGroupId)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SupportGroupExchangeRate::find()
                ->andWhere(['support_group_id' => $supportGroupId]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'supportGroupId' => $supportGroupId,
        ]);
    }

    /**
     * Displays a single SupportGroupExchangeRate model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->supportGroupExchangeRateRepository->findSupportGroupExchangeRate($id),
        ]);
    }

    /**
     * Creates a new SupportGroupExchangeRate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($supportGroupId)
    {
        $model = new SupportGroupExchangeRate();
        $model->support_group_id = $supportGroupId;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'supportGroupId' => $supportGroupId]);
        }

        return $this->render('create', [
            'model' => $model,
            'supportGroupId' => $supportGroupId,
        ]);
    }

    /**
     * Updates an existing SupportGroupExchangeRate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $supportGroupId)
    {
        $model = $this->supportGroupExchangeRateRepository->findSupportGroupExchangeRate($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'supportGroupId' => $supportGroupId]);
        }

        return $this->render('update', [
            'model' => $model,
            'supportGroupId' => $supportGroupId,
        ]);
    }

    /**
     * Deletes an existing SupportGroupExchangeRate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id, $supportGroupId)
    {
        $this->supportGroupExchangeRateRepository->findSupportGroupExchangeRate($id)->delete();

        return $this->redirect(['index', 'supportGroupId' => $supportGroupId]);
    }
}
