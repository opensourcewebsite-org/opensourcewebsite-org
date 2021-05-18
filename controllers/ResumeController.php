<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\Currency;
use app\models\Resume;
use app\models\search\ResumeSearch;
use app\models\WebModels\WebResume;
use Yii;
use yii\filters\AccessControl;
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
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new ResumeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    /**
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new WebResume();
        $model->user_id = Yii::$app->user->identity->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
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
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model, 'currencies' => Currency::find()->all()]);
    }

    public function actionView(int $id): string
    {
        $model = $this->findModelByIdAndCurrentUser($id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionViewLocation(int $id): string
    {
        return $this->renderAjax('view_location_map_modal', ['model' => $this->findModelByIdAndCurrentUser($id)]);
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

}
