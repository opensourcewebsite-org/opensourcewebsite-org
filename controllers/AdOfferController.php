<?php

declare(strict_types=1);

namespace app\controllers;

use app\components\Controller;
use app\models\AdOffer;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\scenarios\AdOffer\SetActiveScenario;
use app\models\scenarios\AdOffer\UpdateKeywordsByIdsScenario;
use app\models\search\AdOfferSearch;
use app\models\User;
use app\repositories\AdOfferRepository;
use app\repositories\AdSearchRepository;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdOfferController extends Controller
{
    public AdOfferRepository $adOfferRepository;
    public AdSearchRepository $adSearchRepository;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->adOfferRepository = new AdOfferRepository();
        $this->adSearchRepository = new AdSearchRepository();
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new AdOfferSearch(['status' => AdOffer::STATUS_ON]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionCreate()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $model = new AdOffer();
        $model->user_id = $user->id;
        $model->currency_id = $user->currency_id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            (new UpdateKeywordsByIdsScenario($model))->run();

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id)
    {
        $model = $this->adOfferRepository->findAdOfferByIdAndCurrentUser($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            (new UpdateKeywordsByIdsScenario($model))->run();

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @return array|bool
     * @throws NotFoundHttpException
     */
    public function actionSetActive(int $id)
    {
        $model = $this->adOfferRepository->findAdOfferByIdAndCurrentUser($id);

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
        $model = $this->adOfferRepository->findAdOfferByIdAndCurrentUser($id);

        $this->response->format = Response::FORMAT_JSON;

        $model->setInactive()->save();

        return true;
    }

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->adOfferRepository->findAdOfferByIdAndCurrentUser($id)
        ]);
    }

    public function actionViewLocation(int $id): string
    {
        return $this->renderAjax('modals/view-location', ['model' => AdOffer::findOne($id)]);
    }

    public function actionShowMatches(int $adSearchId): string
    {
        $model = $this->adSearchRepository->findAdSearchByIdAndCurrentUser($adSearchId);

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

    public function actionViewMatch(int $adSearchId, int $adOfferId): string
    {
        $matchedOffer = $this->adOfferRepository->findMatchedAdOfferByIdAndAdSearch(
            $adOfferId,
            $this->adSearchRepository->findAdSearchByIdAndCurrentUser($adSearchId)
        );

        $matchedOffer->trigger(
            ViewedByUserInterface::EVENT_VIEWED_BY_USER,
            new ViewedByUserEvent(['user' => Yii::$app->user->identity])
        );

        return $this->render('view-match', ['model' => $matchedOffer, 'adSearchId' => $adSearchId]);
    }
}
