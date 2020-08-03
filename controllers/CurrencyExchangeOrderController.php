<?php

namespace app\controllers;

use app\models\Currency;
use Yii;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderPaymentMethod;
use yii\data\ActiveDataProvider;
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
     * @param string $status
     * @return mixed
     */
    public function actionIndex(string $status = CurrencyExchangeOrder::STATUS_ACTIVE)
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
        if ($sell_payments = $order->getCurrencyExchangeOrderPaymentMethod()
            ->where(['type' => 1])
            ->one()) {
            $sell_payment = PaymentMethod::findOne($sell_payments->payment_method_id);
            $sell_payment_name = $sell_payment->name;
        }
        if ($buy_payments = $order->getCurrencyExchangeOrderPaymentMethod()
            ->where(['type' => 2])
            ->one()) {
            $buy_payment = PaymentMethod::findOne($buy_payments->payment_method_id);
            $buy_payment_name = $buy_payment->name;
        }
        return $this->render('view', [
            'model' => $order,
            'sell_payment' => isset($sell_payment_name)?$sell_payment_name:Yii::t('app', 'Not selected'),
            'buy_payment' => isset($buy_payment_name)?$buy_payment_name:Yii::t('app', 'Not selected'),
        ]);
    }

    /**
     * Creates a new CurrencyExchangeOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CurrencyExchangeOrder(['user_id' => Yii::$app->user->identity->id]);

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
                if (!$sell_payment = $order->getCurrencyExchangeOrderPaymentMethod()->where(['type' => 1])->one()) {
                    $sell_payment = new CurrencyExchangeOrderPaymentMethod();
                    $sell_payment->order_id = $order->id;
                    $sell_payment->payment_method_id = $post['sell_payment'];
                    $sell_payment->type = 1;
                }else{
                    $sell_payment->payment_method_id = $post['sell_payment'];
                }
                $sell_payment->save();
            }

            if (!empty($post['buy_payment'])) {
                if (!$buy_payment = $order->getCurrencyExchangeOrderPaymentMethod()->where(['type' => 2])->one()) {
                    $buy_payment = new CurrencyExchangeOrderPaymentMethod();
                    $buy_payment->order_id = $order->id;
                    $buy_payment->payment_method_id = $post['buy_payment'];
                    $buy_payment->type = 2;
                }else{
                    $buy_payment->payment_method_id = $post['buy_payment'];
                }
                $buy_payment->save();
            }

            $order->load($post);
            $order->save();
            return $this->redirect(['view', 'id' => $order->id]);
        }

        $payments_types = ArrayHelper::map( PaymentMethod::find()
            ->asArray()
            ->all(), 'id', 'name');
        $order->setLocation(Yii::$app->request->post()['location'] ?? '');
        $sell_payments = $order->getCurrencyExchangeOrderPaymentMethod()
            ->where(['type' => 1])
            ->one();
        $buy_payments = $order->getCurrencyExchangeOrderPaymentMethod()
            ->where(['type' => 2])
            ->one();

        return $this->render('update', [
            'model' => $order,
            'sell_payment_id' => isset($sell_payments)?$sell_payments->payment_method_id:'',
            'buy_payment_id' => isset($buy_payments)?$buy_payments->payment_method_id:'',
            'payments_types' => $payments_types,
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
