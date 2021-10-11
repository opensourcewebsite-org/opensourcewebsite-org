<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\AdOffer;
use app\models\Currency;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\scenarios\AdSearch\SetActiveScenario;
use app\models\scenarios\AdSearch\UpdateKeywordsByIdsScenario;
use app\models\search\AdSearchSearch;
use app\models\User;
use Yii;
use app\models\AdSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdSearchController extends Controller
{
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

        return $this->render('create', ['model' => $model, 'currencies' => Currency::find()->all()]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModelByIdAndCurrentUser($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            (new UpdateKeywordsByIdsScenario($model))->run();

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model, 'currencies' => Currency::find()->all()]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModelByIdAndCurrentUser($id)]);
    }

    /**
     * @param int $id
     * @return array|bool
     * @throws NotFoundHttpException
     */
    public function actionSetActive(int $id)
    {
        $model = $this->findModelByIdAndCurrentUser($id);

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
        $model = $this->findModelByIdAndCurrentUser($id);

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
        $model = $this->findAdOfferByIdAndCurrentUser($adOfferId);

        if ($model->getMatchesOrderedByUserRating()->exists()) {
            $dataProvider = new ActiveDataProvider([
                'query' => $model->getMatchesOrderedByUserRating(),
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
        $matchedOffer = $this->findMatchedAdSearchByIdAndAdOrder(
            $adSearchId,
            $this->findAdOfferByIdAndCurrentUser($adOfferId)
        );

        $matchedOffer->trigger(
            ViewedByUserInterface::EVENT_VIEWED_BY_USER,
            new ViewedByUserEvent(['user' => Yii::$app->user->identity])
        );

        return $this->render('view-match', ['model' => $matchedOffer, 'adOfferId' => $adOfferId]);
    }

    private function findModelByIdAndCurrentUser(int $id): AdSearch
    {
        /** @var AdSearch $model */
        if ($model = AdSearch::find()
            ->where(['id' => $id])
            ->userOwner()
            ->one()) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }

    private function findAdOfferByIdAndCurrentUser(int $id): AdOffer
    {
        /** @var AdOffer $model */
        if ($model = AdOffer::find()
            ->where(['id' => $id])
            ->userOwner()
            ->one()) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }

    public function findMatchedAdSearchByIdAndAdOrder(int $id, AdOffer $adOffer)
    {
        if ($adSearch = $adOffer->getMatches()->where(['id' => $id])->one()) {
            return $adSearch;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
