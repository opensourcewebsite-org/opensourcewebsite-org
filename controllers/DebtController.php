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
        $userId = Yii::$app->user->id;
        $debtData = Debt::find()
            ->select([
                'id',
                'currency_id',
                'depositAmount' => 'IF(to_user_id = ' . $userId . ', SUM(amount), 0)',
                'creditAmount' => 'IF(from_user_id = ' . $userId . ', SUM(amount), 0)',
            ])
            ->andWhere([
                'OR',
                ['from_user_id' => $userId],
                ['to_user_id' => $userId]
            ])
            ->groupBy(['currency_id', 'from_user_id']);
        $dataProvider = new ActiveDataProvider([
            'query' => Debt::find()
                ->from(['debtData' => $debtData])
                ->select([
                    'id' => 'debtData.id',
                    'currency_id' => 'debtData.currency_id',
                    'deposit' => 'SUM(debtData.depositAmount)',
                    'credit' => 'SUM(debtData.creditAmount)',
                ])
                ->groupBy(['debtData.currency_id']),
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
    public function actionView($id, $direction, $currencyId)
    {
        $userId = Yii::$app->user->id;
        $query = Debt::find()
            ->andWhere(['currency_id' => $currencyId]);
        if ((int) $direction === Debt::DIRECTION_DEPOSIT) {
            $query->andWhere(['to_user_id' => $userId]);
        } else {
            $query->andWhere(['from_user_id' => $userId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'direction' => $direction,
            'dataProvider' => $dataProvider,
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
            $direction = ($model->to_user_id === Yii::$app->user->id) ? Debt::DIRECTION_DEPOSIT : Debt::DIRECTION_CREDIT;
            return $this->redirect(['view', 'id' => $model->id, 'direction' => $direction, 'currencyId' => $model->currency_id]);
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
