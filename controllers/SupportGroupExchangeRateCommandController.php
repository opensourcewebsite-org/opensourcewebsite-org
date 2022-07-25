<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\base\ViewContextInterface;

use app\models\SupportGroupExchangeRateCommand;
use app\repositories\SupportGroupExchangeRateCommandRepository;

/**
 * SupportGroupExchangeRateCommandController implements the CRUD actions for SupportGroupExchangeRateCommand model.
 */
class SupportGroupExchangeRateCommandController extends Controller implements ViewContextInterface
{
    public SupportGroupExchangeRateCommandRepository $supportGroupExchangeRateCommandRepository;

    function __construct()
    {
        parent::__construct(...func_get_args());

        $this->supportGroupExchangeRateCommandRepository = new SupportGroupExchangeRateCommandRepository();
    }

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
            'model' => $this->supportGroupExchangeRateCommandRepository->findSupportGroupExchangeRateCommand($id),
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
        $model = $this->supportGroupExchangeRateCommandRepository->findSupportGroupExchangeRateCommand($id);

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
        $this->supportGroupExchangeRateCommandRepository->findModel($id)->delete();

        return $this->redirect(['index', 'supportGroupExchangeRateId' => $supportGroupExchangeRateId, 'type' => $type]);
    }
}
