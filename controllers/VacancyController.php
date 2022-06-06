<?php

declare(strict_types=1);

namespace app\controllers;

use app\components\Controller;
use app\models\Currency;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\Language;
use app\models\LanguageLevel;
use app\models\Resume;
use app\models\scenarios\Vacancy\SetActiveScenario;
use app\models\scenarios\Vacancy\UpdateKeywordsByIdsScenario;
use app\models\search\VacancySearch;
use app\models\User;
use app\models\Vacancy;
use app\models\VacancyLanguage;
use app\models\WebModels\WebVacancy;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class VacancyController extends Controller
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
        $searchModel = new VacancySearch(['status' => Vacancy::STATUS_ON]);
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

        $model = new WebVacancy();
        $model->user_id = $user->id;
        $model->currency_id = $user->currency_id;
        $model->remote_on = WebVacancy::REMOTE_ON;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            (new UpdateKeywordsByIdsScenario($model))->run();

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'companies' => $model->user->getCompanies()->all(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModelByIdAndCurrentUser($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            (new UpdateKeywordsByIdsScenario($model))->run();

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('update', [
            'model' => $model,
            'companies' => $model->user->getCompanies()->all(),
        ]);
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

        return $this->redirect('/vacancy/index');
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
        return $this->renderAjax('modals/view-location', ['model' => $this->findModel($id)]);
    }

    public function actionChangeLanguage(int $id, int $vacancyId)
    {
        $vacancy = $this->findModelByIdAndCurrentUser($vacancyId);

        $languages = array_map(function ($language) {
            return strtoupper($language->code) . ' - ' . $language->name;
        }, Language::find()->indexBy('id')->orderBy('code ASC')->all());

        $languageLevels = array_map(function ($languageLevel) {
            return (isset($languageLevel->code) ? strtoupper($languageLevel->code) . ' - ' : '') . Yii::t('user', $languageLevel->description);
        }, LanguageLevel::find()->indexBy('id')->orderBy('code ASC')->all());

        $vacancyLanguage = VacancyLanguage::find()
            ->where([
                'id' => $id,
                'vacancy_id' => $vacancy->id,
            ])->one();

        if ($vacancyLanguage && Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            $vacancyLanguage->setAttributes([
                'language_level_id' => $postData['level'],
            ]);

            if ($vacancyLanguage->save()) {
                $vacancy->trigger(Vacancy::EVENT_LANGUAGES_UPDATED);

                return $this->redirect([
                    'view',
                    'id' => $vacancy->id,
                ]);
            }
        }

        $renderParams = [
            'vacancy' => $vacancy,
            'languages' => $languages,
            'languageLevels' => $languageLevels,
            'vacancyLanguage' => $vacancyLanguage,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('modals/change-language', $renderParams);
        } else {
            return $this->render('modals/change-language', $renderParams);
        }
    }

    public function actionAddLanguage(int $vacancyId)
    {
        $vacancy = $this->findModelByIdAndCurrentUser($vacancyId);

        $languages = array_map(function ($language) {
            return strtoupper($language->code) . ' - ' . $language->name;
        }, Language::find()->indexBy('id')->orderBy('code ASC')->all());

        $languageLevels = array_map(function ($languageLevel) {
            return (isset($languageLevel->code) ? strtoupper($languageLevel->code) . ' - ' : '') . Yii::t('user', $languageLevel->description);
        }, LanguageLevel::find()->indexBy('id')->orderBy('code ASC')->all());

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            $vacancyLanguage = VacancyLanguage::find()
                ->where([
                    'vacancy_id' => $vacancy->id,
                    'language_id' => $postData['language'],
                ])
                ->one();

            $vacancyLanguage = $vacancyLanguage ?? new VacancyLanguage();

            $vacancyLanguage->setAttributes([
                'vacancy_id' => $vacancy->id,
                'language_id' => $postData['language'],
                'language_level_id' => $postData['level']
            ]);

            if ($vacancyLanguage->save()) {
                $vacancy->trigger(Vacancy::EVENT_LANGUAGES_UPDATED);

                return $this->redirect([
                    'view',
                    'id' => $vacancy->id,
                ]);
            }
        }

        $renderParams = [
            'vacancy' => $vacancy,
            'languages' => $languages,
            'languageLevels' => $languageLevels,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('modals/add-language', $renderParams);
        } else {
            return $this->render('modals/add-language', $renderParams);
        }
    }

    public function actionDeleteLanguage()
    {
        if (Yii::$app->request->isPost) {
            $id = (int)Yii::$app->request->post('id');
            $vacancyId = (int)Yii::$app->request->post('vacancyId');

            $vacancy = $this->findModelByIdAndCurrentUser($vacancyId);

            if ($vacancy) {
                $vacancyLanguage = VacancyLanguage::find()
                    ->where([
                        'id' => $id,
                        'vacancy_id' => $vacancy->id,
                    ])
                ->one();

                if ($vacancyLanguage) {
                    $vacancyLanguage->delete();

                    $vacancy->trigger(Vacancy::EVENT_LANGUAGES_UPDATED);
                }

                return $this->redirect([
                    'vacancy/view',
                    'id' => $vacancy->id,
                ]);
            }
        }

        return $this->redirect('/vacancy');
    }

    public function actionShowMatches(int $resumeId): string
    {
        $model = $this->findResumeByIdAndCurrentUser($resumeId);

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

    public function actionViewMatch(int $resumeId, int $vacancyId): string
    {
        $matchedVacancy = $this->findMatchedVacancyByIdAndResume(
            $vacancyId,
            $this->findResumeByIdAndCurrentUser($resumeId)
        );

        $matchedVacancy->trigger(
            ViewedByUserInterface::EVENT_VIEWED_BY_USER,
            new ViewedByUserEvent(['user' => Yii::$app->user->identity])
        );

        return $this->render('view-match', ['model' => $matchedVacancy, 'resumeId' => $resumeId]);
    }

    private function findModel(int $id): Vacancy
    {
        if ($model = Vacancy::findOne($id)) {
            return $model;
        }
        throw new NotFoundHttpException('Requested Page Not Found');
    }

    private function findModelByIdAndCurrentUser(int $id): Vacancy
    {
        /** @var WebVacancy $model */
        if ($model = WebVacancy::find()
            ->where(['id' => $id])
            ->userOwner()
            ->one()) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }

    private function findResumeByIdAndCurrentUser(int $id): Resume
    {
        /** @var Resume $model */
        if ($model = Resume::find()
            ->where(['id' => $id])
            ->userOwner()
            ->one()) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
    private function findMatchedVacancyByIdAndResume(int $id, Resume $resume)
    {
        if ($vacancy = $resume->getMatches()->where(['id' => $id])->one()) {
            return $vacancy;
        }
        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
