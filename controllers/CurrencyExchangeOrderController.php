<?php

namespace app\controllers;

use app\models\Currency;
use Yii;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderPaymentMethod;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \app\components\helpers\ArrayHelper;
use \app\models\PaymentMethod;

/**
 * CurrencyExchangeOrderController implements the CRUD actions for CurrencyExchangeOrder model.
 */
class CurrencyExchangeOrderController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all CurrencyExchangeOrder models.
     * @param int $status
     * @return mixed
     */
    public function actionIndex(int $status = CurrencyExchangeOrder::STATUS_ACTIVE)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CurrencyExchangeOrder::find()
                ->where(['status' => $status])
                ->orderBy(['selling_currency_id' => SORT_ASC, 'created_at' => SORT_DESC]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CurrencyExchangeOrder model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $order = $this->findModel($id);
        if ($sellPayments = $order->getCurrencyExchangeOrderPaymentMethod()
            ->where(['type' => 1])
            ->one()) {
            $sellPayment = PaymentMethod::findOne($sellPayments->payment_method_id);
            $sellPaymentName = $sellPayment->name;
        }
        if ($buyPayments = $order->getCurrencyExchangeOrderPaymentMethod()
            ->where(['type' => 2])
            ->one()) {
            $buyPayment = PaymentMethod::findOne($buyPayments->payment_method_id);
            $buyPaymentName = $buyPayment->name;
        }
        return $this->render('view', [
            'model' => $order,
            'sellPayment' => isset($sellPaymentName)?$sellPaymentName:Yii::t('app', 'Not selected'),
            'buyPayment' => isset($buyPaymentName)?$buyPaymentName:Yii::t('app', 'Not selected'),
        ]);
    }

    /**
     * Creates a new CurrencyExchangeOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CurrencyExchangeOrder();
        $model->user_id = Yii::$app->user->identity->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'currency' => Currency::find()->all(),
        ]);
    }

    /**
     * Updates an existing CurrencyExchangeOrder model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $order = $this->findModel($id);
        if ($post = Yii::$app->request->post()) {
            if (!empty($post['sell_payment'])) {
                if (!$sellPayment = $order->getCurrencyExchangeOrderPaymentMethod()->where(['type' => 1])->one()) {
                    $sellPayment = new CurrencyExchangeOrderPaymentMethod();
                    $sellPayment->order_id = $order->id;
                    $sellPayment->payment_method_id = $post['sell_payment'];
                    $sellPayment->type = 1;
                } else {
                    $sellPayment->payment_method_id = $post['sell_payment'];
                }
                $sellPayment->save();
            }

            if (!empty($post['buy_payment'])) {
                if (!$buyPayment = $order->getCurrencyExchangeOrderPaymentMethod()->where(['type' => 2])->one()) {
                    $buyPayment = new CurrencyExchangeOrderPaymentMethod();
                    $buyPayment->order_id = $order->id;
                    $buyPayment->payment_method_id = $post['buy_payment'];
                    $buyPayment->type = 2;
                } else {
                    $buyPayment->payment_method_id = $post['buy_payment'];
                }
                $buyPayment->save();
            }

            $order->load($post);
            $order->save();

            return $this->redirect(['view', 'id' => $order->id]);
        }

        $paymentsTypes = ArrayHelper::map(PaymentMethod::find()
            ->asArray()
            ->all(), 'id', 'name');
        $order->setLocation(Yii::$app->request->post()['location'] ?? '');
        $sellPayments = $order->getCurrencyExchangeOrderPaymentMethod()
            ->where(['type' => 1])
            ->one();
        $buyPayments = $order->getCurrencyExchangeOrderPaymentMethod()
            ->where(['type' => 2])
            ->one();

        return $this->render('update', [
            'model' => $order,
            'sellPaymentId' => isset($sellPayments) ? $sellPayments->payment_method_id : '',
            'buyPaymentId' => isset($buyPayments) ? $buyPayments->payment_method_id : '',
            'paymentsTypes' => $paymentsTypes,
        ]);
    }

    /**
     * Change status.
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionStatus($id)
    {
        if (Yii::$app->request->isAjax) {
            $postdata = Yii::$app->request->post();
            $model = $this->findModel($id);

            if ($postdata['status'] && $notFilledFields = $model->notPossibleToChangeStatus()) {
                return json_encode($notFilledFields);
            }
            $model->status = $postdata['status'];
            return $model->save();
        }
        return false;
    }

    /**
     * Deletes an existing CurrencyExchangeOrder model.
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
     * Finds the CurrencyExchangeOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CurrencyExchangeOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CurrencyExchangeOrder::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
