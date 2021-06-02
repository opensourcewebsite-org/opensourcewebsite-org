<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\FormModels\LanguageWithLevelsForm;
use app\models\JobVacancyKeyword;
use app\models\scenarios\Vacancy\UpdateKeywordsByIdsScenario;
use app\models\scenarios\Vacancy\UpdateLanguagesScenario;
use app\models\Currency;
use app\models\scenarios\Vacancy\SetActiveScenario;
use app\models\User;
use app\models\Vacancy;
use app\models\WebModels\WebVacancy;
use app\models\search\VacancySearch;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class VacancyController extends Controller {

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
        $model = new WebVacancy();
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $model->user_id = $user->id;
        $model->currency_id = $user->currency_id;
        $languageWithLevelsForm = new LanguageWithLevelsForm(['required' => true]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            (new UpdateKeywordsByIdsScenario($model))->run();

            if ($languageWithLevelsForm->load(Yii::$app->request->post())) {
                (new UpdateLanguagesScenario($model, $languageWithLevelsForm))->run();
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'currencies' => Currency::find()->all(),
            'companies' => $model->globalUser->getCompanies()->all(),
            'languageWithLevelsForm' => $languageWithLevelsForm
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModelByIdAndCurrentUser($id);

        $languageWithLevelsForm = new LanguageWithLevelsForm();
        $languageWithLevelsForm->setSelectedLanguages($model->languagesWithLevels);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            (new UpdateKeywordsByIdsScenario($model))->run();

            if ($languageWithLevelsForm->load(Yii::$app->request->post())) {
                (new UpdateLanguagesScenario($model, $languageWithLevelsForm))->run();
            }

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('update', [
            'model' => $model,
            'currencies' => Currency::find()->all(),
            'companies' => $model->globalUser->getCompanies()->all(),
            'languageWithLevelsForm' => $languageWithLevelsForm
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
        return $this->renderAjax('view_location_map_modal', ['model' => $this->findModelByIdAndCurrentUser($id)]);
    }

    public function actionUpdateLanguages(int $id)
    {
        $vacancy = $this->findModelByIdAndCurrentUser($id);

        $languageWithLevelsForm = new LanguageWithLevelsForm();
        $languageWithLevelsForm->setSelectedLanguages($vacancy->languagesWithLevels);

        if ($languageWithLevelsForm->load(Yii::$app->request->post())) {
            (new UpdateLanguagesScenario($vacancy, $languageWithLevelsForm))->run();

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->renderAjax('update_languages', [
            'model' => $languageWithLevelsForm
        ]);
    }

    private function findModelByIdAndCurrentUser(int $id): Vacancy
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
}
