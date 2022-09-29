<?php

namespace app\controllers;

use app\components\Controller;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderMatch;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\scenarios\CurrencyExchangeOrder\SetActiveScenario;
use app\models\scenarios\CurrencyExchangeOrder\UpdateBuyingPaymentMethodsByIdsScenario;
use app\models\scenarios\CurrencyExchangeOrder\UpdateSellingPaymentMethodsByIdsScenario;
use app\models\search\CurrencyExchangeOrderSearch;
use app\repositories\CurrencyExchangeOrderRepository;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CurrencyExchangeOrderController implements the CRUD actions for CurrencyExchangeOrder model.
 */
class CurrencyExchangeOrderController extends Controller
{
    public CurrencyExchangeOrderRepository $currencyExchangeOrderRepository;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->currencyExchangeOrderRepository = new CurrencyExchangeOrderRepository();
    }

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

    public function actionIndex()
    {
        $searchModel = new CurrencyExchangeOrderSearch(['status' => CurrencyExchangeOrder::STATUS_ON]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
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
        $model = $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id);

        return $this->render('view', ['model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        $model = $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id);

        $model->delete();

        return $this->redirect('/currency-exchange-order/index');
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
        $model = $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionUpdateSellingPaymentMethods(int $id)
    {
        $model = $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id);

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $model->load($postData)) {
            if ($model->validate(['sellingPaymentMethodIds'])) {
                (new UpdateSellingPaymentMethodsByIdsScenario($model))->run();

                return $this->redirect([
                    'view',
                    'id' => $model->id,
                ]);
            }
        }

        $renderParams = [
            'model' => $model,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('modals/update-selling-methods', $renderParams);
        } else {
            return $this->render('modals/update-selling-methods', $renderParams);
        }
    }

    public function actionUpdateBuyingPaymentMethods(int $id)
    {
        $model = $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id);

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $model->load($postData)) {
            if ($model->validate(['buyingPaymentMethodIds'])) {
                (new UpdateBuyingPaymentMethodsByIdsScenario($model))->run();

                return $this->redirect([
                    'view',
                    'id' => $model->id,
                ]);
            }
        }

        $renderParams = [
            'model' => $model,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('modals/update-buying-methods', $renderParams);
        } else {
            return $this->render('modals/update-buying-methods', $renderParams);
        }
    }

    /**
     * @param int $id
     * @return array|bool
     * @throws NotFoundHttpException
     */
    public function actionSetActive(int $id)
    {
        $model = $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id);

        $this->response->format = Response::FORMAT_JSON;

        $scenario = new SetActiveScenario($model);

        if ($scenario->run()) {
            $model->save();
            return true;
        }

        return $scenario->getErrors();
    }

    public function actionSetInactive(int $id): bool
    {
        $model = $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id);

        $this->response->format = Response::FORMAT_JSON;

        $model->setInactive()->save();

        return true;
    }

    public function actionViewOrderSellingLocation(int $id): string
    {
        return $this->renderAjax(
            'modals/view-location',
            [
                'model' => $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id),
                'type' => 'sell'
            ]
        );
    }

    public function actionViewOrderBuyingLocation(int $id): string
    {
        return $this->renderAjax(
            'modals/view-location',
            [
                'model' => $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id),
                'type' => 'buy'
            ]
        );
    }

    public function actionShowMatches(int $id): string
    {
        $model = $this->currencyExchangeOrderRepository->findCurrencyExchangeOrderByIdAndCurrentUser($id);

        if ($model->getMatchesOrderByRank()->exists()) {
            $dataProvider = new ActiveDataProvider([
                'query' => $model->getMatchesOrderByRank(),
            ]);

            return $this->render('matches', [
                'dataProvider' => $dataProvider,
                'model' => $model,
            ]);
        }

        throw new NotFoundHttpException('Currently no matched Offers found.');
    }

    public function actionViewMatch(int $order_id, int $match_order_id): string
    {
        /** @var CurrencyExchangeOrderMatch $matchModel */
        $matchModel = CurrencyExchangeOrderMatch::find()
            ->where(['order_id' => $order_id, 'match_order_id' => $match_order_id])
            ->one();

        $matchModel->matchOrder->trigger(
            ViewedByUserInterface::EVENT_VIEWED_BY_USER,
            new ViewedByUserEvent(['user' => Yii::$app->user->identity])
        );

        if ($matchModel) {
            return $this->render('view-match', [
                'orderModel' => $matchModel->order,
                'matchOrderModel' => $matchModel->matchOrder,
            ]);
        }

        throw new NotFoundHttpException('No offer found with current orders combination!');
    }
}
