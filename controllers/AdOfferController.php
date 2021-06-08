<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\Currency;
use app\models\scenarios\AdOffer\UpdateKeywordsByIdsScenario;
use app\models\scenarios\AdOffer\SetActiveScenario;
use app\models\User;
use Yii;
use app\models\AdOffer;
use app\models\search\AdOfferSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdOfferController extends Controller {

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
        /** @var User $user */
        $user = Yii::$app->user->identity;

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
        return $this->renderAjax('view_location_map_modal', ['model' => $this->findModelByIdAndCurrentUser($id)]);
    }

    private function findModelByIdAndCurrentUser(int $id): AdOffer
    {
        /** @var AdOffer $model */
        if ($model = AdOffer::find()
            ->where(['id' => $id])
            ->andWhere(['user_id' => Yii::$app->user->identity->id])
            ->one()) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
