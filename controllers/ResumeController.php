<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\Vacancy;
use app\models\WebModels\WebVacancy;
use Yii;
use app\models\Currency;
use app\models\Resume;
use app\models\scenarios\Resume\UpdateKeywordsByIdsScenario;
use app\models\scenarios\Resume\SetActiveScenario;
use app\models\search\ResumeSearch;
use app\models\User;
use app\models\WebModels\WebResume;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ResumeController extends Controller
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

    public function actionIndex(): string
    {
        $searchModel = new ResumeSearch(['status' => Resume::STATUS_ON]);
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

        $model = new WebResume();
        $model->user_id = $user->id;
        $model->currency_id = $user->currency_id;
        $model->remote_on = 1;
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

    public function actionView(int $id): string
    {
        $model = $this->findModelByIdAndCurrentUser($id);

        return $this->render('view', ['model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        $model = $this->findModelByIdAndCurrentUser($id);

        $model->delete();

        return $this->redirect('/resume/index');
    }

    public function actionViewLocation(int $id): string
    {
        return $this->renderAjax('view_location_map_modal', ['model' => $this->findModelByIdAndCurrentUser($id)]);
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

    public function actionShowMatches(int $vacancyId): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->findVacancyByIdAndCurrentUser($vacancyId)->getMatches()
        ]);

        return $this->render('matches', ['vacancyId' => $vacancyId, 'dataProvider' => $dataProvider]);
    }

    public function actionViewMatch(int $vacancyId, int $resumeId): string
    {
        $matchedResume = $this->findMatchedResumeByIdAndVacancy(
            $resumeId,
            $this->findVacancyByIdAndCurrentUser($vacancyId)
        );

        return $this->render('view-match', ['model' => $matchedResume, 'vacancyId' => $vacancyId]);
    }

    private function findModelByIdAndCurrentUser(int $id): Resume
    {
        /** @var WebResume $model */
        if ($model = WebResume::find()
            ->where(['id' => $id])
            ->andWhere(['user_id' => Yii::$app->user->identity->id])
            ->one()) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }

    private function findVacancyByIdAndCurrentUser(int $id)
    {
        /** @var WebVacancy $model */
        if ($model = WebVacancy::find()
            ->where(['id' => $id])
            ->andWhere(['user_id' => Yii::$app->user->identity->id])
            ->one()) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }

    public function findMatchedResumeByIdAndVacancy(int $id, Vacancy $vacancy)
    {
        if ($resume = $vacancy->getMatches()->where(['id' => $id])->one()) {
            return $resume;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
