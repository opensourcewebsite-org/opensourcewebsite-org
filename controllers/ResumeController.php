<?php

declare(strict_types=1);

namespace app\controllers;

use app\components\Controller;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\Resume;
use app\models\scenarios\Resume\SetActiveScenario;
use app\models\scenarios\Resume\UpdateKeywordsByIdsScenario;
use app\models\search\ResumeSearch;
use app\models\User;
use app\models\Vacancy;
use app\models\WebModels\WebResume;
use app\repositories\ResumeRepository;
use app\repositories\VacancyRepository;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ResumeController extends Controller
{
    public ResumeRepository $resumeRepository;
    public VacancyRepository $vacancyRepository;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->resumeRepository = new ResumeRepository();
        $this->vacancyRepository = new VacancyRepository();
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
        $model->remote_on = WebResume::REMOTE_ON;

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
        $model = $this->resumeRepository->findResumeByIdAndCurrentUser($id);

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
        $model = $this->resumeRepository->findResumeByIdAndCurrentUser($id);

        return $this->render('view', ['model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        $model = $this->resumeRepository->findResumeByIdAndCurrentUser($id);

        $model->delete();

        return $this->redirect('/resume/index');
    }

    public function actionViewLocation(int $id): string
    {
        return $this->renderAjax('modals/view-location', ['model' => $this->resumeRepository->findResumeByIdAndCurrentUser($id)]);
    }

    /**
     * @param int $id
     * @return array|bool
     * @throws NotFoundHttpException
     */
    public function actionSetActive(int $id)
    {
        $model = $this->resumeRepository->findResumeByIdAndCurrentUser($id);

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
        $model = $this->resumeRepository->findResumeByIdAndCurrentUser($id);

        $this->response->format = Response::FORMAT_JSON;

        $model->setInactive()->save();

        return true;
    }

    public function actionShowMatches(int $vacancyId): string
    {
        $model = $this->vacancyRepository->findVacancyByIdAndCurrentUser($vacancyId);

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

    public function actionViewMatch(int $vacancyId, int $resumeId): string
    {
        $matchedResume = $resumeRepository->findMatchedResumeByIdAndVacancy(
            $resumeId,
            $vacancyRepository->findVacancyByIdAndCurrentUser($vacancyId)
        );

        $matchedResume->trigger(
            ViewedByUserInterface::EVENT_VIEWED_BY_USER,
            new ViewedByUserEvent(['user' => Yii::$app->user->identity])
        );

        return $this->render('view-match', ['model' => $matchedResume, 'vacancyId' => $vacancyId]);
    }
}
