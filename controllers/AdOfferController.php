<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\AdSearch;
use app\models\Currency;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\scenarios\AdOffer\UpdateKeywordsByIdsScenario;
use app\models\scenarios\AdOffer\SetActiveScenario;
use app\models\User;
use Yii;
use app\models\AdOffer;
use app\models\search\AdOfferSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdOfferController extends Controller
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

        return $this->render('create', ['model' => $model, 'currencies' => Currency::find()->all()]);
    }

    /**
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModelByIdAndCurrentUser($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            (new UpdateKeywordsByIdsScenario($model))->run();

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model, 'currencies' => Currency::find()->all()]);
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

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModelByIdAndCurrentUser($id)
        ]);
    }

    public function actionViewLocation(int $id): string
    {
        return $this->renderAjax('view_location_map_modal', ['model' => AdOffer::findOne($id)]);
    }

    public function actionShowMatches(int $adSearchId): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->findAdSearchByIdAndCurrentUser($adSearchId)->getMatches()
        ]);

        return $this->render('matches', ['adSearchId' => $adSearchId, 'dataProvider' => $dataProvider]);
    }

    public function actionViewMatch(int $adSearchId, int $adOfferId): string
    {
        $matchedOffer = $this->findMatchedAdOfferByIdAndAdSearch(
            $adOfferId,
            $this->findAdSearchByIdAndCurrentUser($adSearchId)
        );

        $matchedOffer->trigger(
            ViewedByUserInterface::EVENT_VIEWED_BY_USER,
            new ViewedByUserEvent(['user' => Yii::$app->user->identity])
        );

        return $this->render('view-match', ['model' => $matchedOffer, 'adSearchId' => $adSearchId]);
    }

    private function findModelByIdAndCurrentUser(int $id): AdOffer
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

    private function findAdSearchByIdAndCurrentUser(int $id): AdSearch
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

    public function findMatchedAdOfferByIdAndAdSearch(int $id, AdSearch $adSearch)
    {
        if ($adOffer = $adSearch->getMatches()->where(['id' => $id])->one()) {
            return $adOffer;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
