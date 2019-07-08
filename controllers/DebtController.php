<?php

namespace app\controllers;

use Yii;
use app\models\Debt;
use app\models\User;
use yii\web\Controller;
use app\models\Currency;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * DebtController implements the CRUD actions for Debt model.
 */
class DebtController extends Controller
{

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
     * Lists all Debt models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Debt::find(),
        ]);

        return $this->render('index', [
                'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Debt model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id, $currencyId)
    {
        $userId = Yii::$app->user->id;
        $depositDataProvider = new ActiveDataProvider([
            'query' => Debt::find()->andWhere([
                'to_user_id' => $userId,
                'currency_id' => $currencyId,
            ]),
        ]);
        $creditDataProvider = new ActiveDataProvider([
            'query' => Debt::find()->andWhere([
                'from_user_id' => $userId,
                'currency_id' => $currencyId,
            ]),
        ]);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'depositDataProvider' => $depositDataProvider,
            'creditDataProvider' => $creditDataProvider,
        ]);
    }

    /**
     * Creates a new Debt model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Debt();
        $user = User::find()
            ->joinWith('contact')
            ->andWhere(['status' => User::STATUS_ACTIVE])
            ->andWhere(['NOT', ['link_user_id' => null]])
            ->all();

        if ($model->load(Yii::$app->request->post())) {
            $model->status = Debt::STATUS_PENDING;
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'user' => $user,
            'currency' => Currency::find()->all(),
        ]);
    }

    /**
     * Updates an existing Debt model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->valid_from_date = (new \DateTime($model->valid_from_date))->format('m/d/Y');
        $model->valid_from_time = (new \DateTime($model->valid_from_time))->format('H:i');
        $user = User::find()
            ->joinWith('contact')
            ->andWhere(['status' => User::STATUS_ACTIVE])
            ->andWhere(['NOT', ['link_user_id' => null]])
            ->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'user' => $user,
            'currency' => Currency::find()->all(),
        ]);
    }

    /**
     * Deletes an existing Debt model.
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
     * Finds the Debt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Debt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Debt::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
