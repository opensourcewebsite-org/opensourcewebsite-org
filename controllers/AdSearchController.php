<?php

declare(strict_types=1);

namespace app\controllers;

use app\components\Controller;
use app\models\AdSearch;
use app\models\Currency;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\scenarios\AdSearch\SetActiveScenario;
use app\models\scenarios\AdSearch\UpdateKeywordsByIdsScenario;
use app\models\search\AdSearchSearch;
use app\models\User;
use app\repositories\AdOfferRepository;
use app\repositories\AdSearchRepository;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdSearchController extends Controller
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
        $searchModel = new AdSearchSearch(['status' => AdSearch::STATUS_ON]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $model = new AdSearch();
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

    public function actionUpdate(int $id)
    {
        $model = $this->adSearchRepository->findAdSearchByIdAndCurrentUser($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            (new UpdateKeywordsByIdsScenario($model))->run();

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionView(int $id): string
    {
        $model = $this->adSearchRepository->findAdSearchByIdAndCurrentUser($id);

        return $this->render('view', ['model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        $model = $this->adSearchRepository->findAdSearchByIdAndCurrentUser($id);

        $model->delete();

        return $this->redirect('/ad-search/index');
    }

    /**
     * @param int $id
     * @return array|bool
     * @throws NotFoundHttpException
     */
    public function actionSetActive(int $id)
    {
        $model = $this->adSearchRepository->findAdSearchByIdAndCurrentUser($id);

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
        $model = $this->adSearchRepository->findAdSearchByIdAndCurrentUser($id);

        $this->response->format = Response::FORMAT_JSON;

        $model->setInactive()->save();

        return true;
    }

    public function actionViewLocation(int $id): string
    {
        return $this->renderAjax('modals/view-location', ['model' => AdSearch::findOne($id)]);
    }

    public function actionShowMatches(int $adOfferId): string
    {
        $model = $this->adOfferRepository->findAdOfferByIdAndCurrentUser($adOfferId);

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
        $matchedOffer = $this->adSearchRepository->findMatchedAdSearchByIdAndAdOrder(
            $adSearchId,
            $this->adOfferRepository->findAdOfferByIdAndCurrentUser($adOfferId)
        );

        $matchedOffer->trigger(
            ViewedByUserInterface::EVENT_VIEWED_BY_USER,
            new ViewedByUserEvent(['user' => Yii::$app->user->identity])
        );

        return $this->render('view-match', ['model' => $matchedOffer, 'adOfferId' => $adOfferId]);
    }
}
