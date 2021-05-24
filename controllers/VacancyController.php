<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\scenarios\Vacancy\SetActiveScenario;
use app\models\Vacancy;
use app\models\WebModels\WebVacancy;
use Yii;
use app\models\search\VacancySearch;
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
        ];
    }

    public function actionCreate()
    {

    }

    public function actionUpdate()
    {

    }

    public function actionView()
    {

    }

    public function actionIndex(): string
    {

        $searchModel = new VacancySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
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
