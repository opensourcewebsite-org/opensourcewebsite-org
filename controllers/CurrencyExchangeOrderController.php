<?php

namespace app\controllers;

use Yii;
use app\models\Currency;
use app\models\CurrencyExchangeOrderMatch;
use app\models\FormModels\CurrencyExchange\OrderPaymentMethods;
use app\services\CurrencyExchangeService;
use app\models\CurrencyExchangeOrder;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \app\models\PaymentMethod;
use yii\web\Response;

/**
 * CurrencyExchangeOrderController implements the CRUD actions for CurrencyExchangeOrder model.
 */
class CurrencyExchangeOrderController extends Controller
{

    protected CurrencyExchangeService $service;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
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
                    'set-active' => ['POST'],
                    'set-inactive' => ['POST'],
                ],
            ],
        ];
    }

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->service = new CurrencyExchangeService();
    }

    /**
     * Lists all CurrencyExchangeOrder models.
     * @param int $status
     * @return mixed
     */
    public function actionIndex(int $status = CurrencyExchangeOrder::STATUS_ON)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CurrencyExchangeOrder::find()
                ->where(['status' => $status])
                ->andWhere(['user_id' => Yii::$app->user->identity->id])
                ->orderBy(['id' => SORT_ASC]),
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
    public function actionView(int $id)
    {
        $order = $this->findModelByIdAndCurrentUser($id);

        return $this->render('view', [
            'model' => $order,
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

        if ($model->load(($post = Yii::$app->request->post())) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CurrencyExchangeOrder model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModelByIdAndCurrentUser($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public function actionUpdateSellMethods($id)
    {
        $model = $this->findModelByIdAndCurrentUser($id);

        $formModel = new OrderPaymentMethods(['order' => $model]);

        if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            $this->service->updatePaymentMethods($model, $formModel->sellingPaymentMethods, []);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('update_sell_methods', [
            'model' => $formModel,
            'paymentsSellTypes' => $this->getPaymentMethodsForCurrency($model->selling_currency_id)
        ]);
    }

    public function actionUpdateBuyMethods($id)
    {
        $model = $this->findModelByIdAndCurrentUser($id);

        $formModel = new OrderPaymentMethods(['order' => $model]);

        if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            $this->service->updatePaymentMethods($model, [], $formModel->buyingPaymentMethods);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('update_buy_methods', [
            'model' => $formModel,
            'paymentsBuyTypes' => $this->getPaymentMethodsForCurrency($model->buying_currency_id),
        ]);
    }

    private function getPaymentMethodsForCurrency(int $currency_id)
    {
        return PaymentMethod::find()->joinWith('currencies')
            ->where(['currency.id' => $currency_id])
            ->all();
    }

    /**
     * @param int $id
     * @return array|bool
     * @throws NotFoundHttpException
     */
    public function actionSetActive(int $id)
    {
        $order = $this->findModelByIdAndCurrentUser($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$order->isActive()) {
            if ($notFilledFields = $order->notPossibleToChangeStatus()) {
                return $notFilledFields;
            }
            $order->setActive()->save();
        }
        return true;
    }

    public function actionSetInactive(int $id): bool
    {
        $order = $this->findModelByIdAndCurrentUser($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        $order->setInactive()->save();

        return true;
    }

    public function actionDelete(int $id): Response
    {
        $this->findModelByIdAndCurrentUser($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionViewOrderSellingLocation(int $id): string
    {
        return $this->renderAjax('map_modal', ['model' => $this->findModelByIdAndCurrentUser($id),'type' => 'sell']);
    }

    public function actionViewOrderBuyingLocation(int $id): string
    {
        return $this->renderAjax('map_modal', ['model' => $this->findModelByIdAndCurrentUser($id),'type' => 'buy']);
    }

    public function actionShowMatches(int $id): string
    {
        $model = $this->findModelByIdAndCurrentUser($id);
        if ($model->getMatchesOrderedByUserRating()->exists()){
            $dataProvider = new ActiveDataProvider([
                'query' => $model->getMatchesOrderedByUserRating(),
            ]);
            $dataProvider->pagination->pageSize = 15;

            return $this->render('matches', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
        throw new NotFoundHttpException('Currently no matched Offers found.');
    }

    public function actionViewMatch(int $order_id, int $match_order_id): string
    {

        /** @var CurrencyExchangeOrderMatch $matchModel */
        $matchModel = CurrencyExchangeOrderMatch::find()
            ->where(['order_id' => $order_id, 'match_order_id' => $match_order_id])
            ->one();

        if ($matchModel) {
            return $this->render('view-match', ['orderModel' => $matchModel->order, 'matchOrderModel' => $matchModel->matchOrder]);
        }

        throw new NotFoundHttpException('No offer found with current orders combination!');
    }

    protected function findModelByIdAndCurrentUser(int $id): CurrencyExchangeOrder
    {
        /** @var CurrencyExchangeOrder $model */
        if ($model = CurrencyExchangeOrder::find()
            ->where(['id' => $id])
            ->andWhere(['user_id' => Yii::$app->user->identity->id])
            ->one()) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
