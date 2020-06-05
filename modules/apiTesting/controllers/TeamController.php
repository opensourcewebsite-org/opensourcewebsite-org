<?php

namespace app\modules\apiTesting\controllers;

use app\models\User;
use app\modules\apiTesting\models\ApiTestProject;
use app\modules\apiTesting\models\ApiTestTeam;
use app\modules\apiTesting\models\ApiTestTeamSearch;
use app\modules\apiTesting\services\ProjectTeamService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class TeamController
 *
 * @package app\modules\apiTesting\controllers
 * @property ProjectTeamService $projectTeamService
 */
class TeamController extends Controller
{
    private $projectTeamService;

    public function init()
    {
        parent::init();
        $this->projectTeamService = new ProjectTeamService();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex($id)
    {
        $searchModel = new ApiTestTeamSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => [
            'project_id' => $id
        ]]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'project' => $this->findProject($id)
        ]);
    }

    public function actionInvite($project_id)
    {
        $model = new ApiTestTeam([
            'project_id' => $project_id
        ]);

        $users = User::find()
            ->joinWith('contact')
            ->active()
            ->andWhere(['NOT', ['link_user_id' => null]])
            ->all();

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($this->projectTeamService->inviteUserToProject($model)) {
                Yii::$app->session->setFlash('success', 'User has been invited');
                $this->redirect(['team/index', 'id' => $project_id]);
            }
        }

        return $this->render('invite', [
            'users' => $users,
            'model' => $model,
            'project' => $this->findProject($project_id)
        ]);
    }

    public function actionAcceptInvite($id)
    {
        $model = $this->findMyModel($id);
        $this->projectTeamService->acceptInvite($model);
        $this->redirect(['/apiTesting/project/testing', 'id' => $model->project_id]);
    }

    public function actionDeclineInvite($id)
    {
        $model = $this->findMyModel($id);
        $this->projectTeamService->declineInvite($model);
        $this->redirect(['/account']);
    }

    public function actionLeave($id)
    {
        $model = $this->findMyModel($id);
        $this->projectTeamService->leaveTeam($model);
        $this->redirect(['/apiTesting/project']);
    }

    private function findProject($id)
    {
        $project = ApiTestProject::find()->my()->andWhere(['id' => $id])->one();
        if ( ! $project) {
            throw new NotFoundHttpException();
        }
        return $project;
    }

    private function findMyModel($id)
    {
        $project = ApiTestTeam::find()
            ->andWhere(['project_id' => $id])
            ->andWhere(['user_id' => Yii::$app->user->id])
            ->one();
        if ( ! $project) {
            throw new NotFoundHttpException();
        }
        return $project;
    }
}
