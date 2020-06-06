<?php

namespace app\modules\apiTesting\controllers;

use app\modules\apiTesting\models\ApiTestProject;
use app\modules\apiTesting\models\ApiTestRunner;
use app\modules\apiTesting\models\ApiTestRunnerSearch;
use app\modules\apiTesting\models\GraphFilterForm;
use app\modules\apiTesting\services\RunnerScheduleManager;
use Yii;
use yii\base\DynamicModel;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * RunnerController implements the CRUD actions for ApiTestRunner model.
 */
class RunnerController extends Controller
{
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

    /**
     * Lists all ApiTestRunner models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $searchModel = new ApiTestRunnerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->byProject($id);
        $dataProvider->query->orderBy('id DESC');
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'project' => $this->findProject($id)
        ]);
    }

    public function actionGraphs($id)
    {
        $filterModel = new GraphFilterForm();
        $filterModel->load(Yii::$app->request->post());
        $server_id = $filterModel->server_id;
        $id = $filterModel->id ?? $id;
        $project = $this->findProject($id);

        if ($filterModel->server_id) {
            $server = $project->getServers()->andWhere(['id' => $server_id])->one();
            if ($server == null) {
                throw new NotFoundHttpException('Not found');
            }
        }
        
        $totalForAllTime = ApiTestRunner::find()->byProject($id)->byServer($server_id)->count();
        $successfulForAllTime = ApiTestRunner::find()->byProject($id)->byServer($server_id)->byStatus(ApiTestRunner::STATUS_SUCCESS)->count();
        $failedForAllTime = ApiTestRunner::find()->byProject($id)->byServer($server_id)->byStatus(ApiTestRunner::STATUS_FAILED)->count();

        if ($totalForAllTime > 0) {
            $successRatio = $successfulForAllTime / ($totalForAllTime / 100);
        } else {
            $successRatio = 0;
        }

        $weekBegin = new \DateTime();
        $weekBegin->modify('-7 days');
        $weekEnd = new \DateTime();
        $weekEnd->modify('+2 day');
        $interval = new \DateInterval('P1D');
        $lastWeekRange = new \DatePeriod($weekBegin, $interval, $weekEnd);
        $lastWeekJobsSuccess = [];
        $lastWeekJobsFailed = [];

        /** @var \DateTime $date */
        foreach ($lastWeekRange as $date) {
            $lastWeekJobsSuccess[] = [$date->getTimestamp(), ApiTestRunner::find()->byProject($id)->byServer($server_id)->andWhere([
                'from_unixtime(start_at,\'%Y-%m-%d\')' => $date->format('Y-m-d')
            ])->byStatus(ApiTestRunner::STATUS_SUCCESS)->count()];

            $lastWeekJobsFailed[] = [$date->getTimestamp(), ApiTestRunner::find()->byProject($id)->byServer($server_id)->andWhere([
                'from_unixtime(start_at,\'%Y-%m-%d\')' => $date->format('Y-m-d')
            ])->byStatus(ApiTestRunner::STATUS_FAILED)->count()];
        }

        $lastWeekDataset = [
            $lastWeekJobsSuccess,
            $lastWeekJobsFailed
        ];

        $monthBegin = new \DateTime();
        $monthBegin->modify('-1 month');
        $monthEnd = new \DateTime();
        $monthEnd->modify('+2 day');
        $interval = new \DateInterval('P1D');
        $lastMonthRange = new \DatePeriod($monthBegin, $interval, $monthEnd);
        $lastMonthSuccess = [];
        $lastMonthFailed = [];

        foreach ($lastMonthRange as $date) {
            $lastMonthSuccess[] = [
                $date->getTimestamp(),
                ApiTestRunner::find()
                    ->byProject($id)->byServer($server_id)
                    ->andWhere(['from_unixtime(start_at,\'%Y-%m-%d\')' => $date->format('Y-m-d')])
                    ->byStatus(ApiTestRunner::STATUS_SUCCESS)
                    ->count()
            ];

            $lastMonthFailed[] = [
                $date->getTimestamp(),
                ApiTestRunner::find()
                    ->byProject($id)->byServer($server_id)
                    ->andWhere(['from_unixtime(start_at,\'%Y-%m-%d\')' => $date->format('Y-m-d')])
                    ->byStatus(ApiTestRunner::STATUS_FAILED)
                    ->count()
            ];
        }

        $lastMonthDataset = [
            $lastMonthSuccess,
            $lastMonthFailed
        ];

        $yearBegin = new \DateTime();
        $yearBegin->modify('-1 year');
        $yearEnd = new \DateTime();
        $yearEnd->modify('+2 month');
        $interval = new \DateInterval('P1M');
        $lastYearRange = new \DatePeriod($yearBegin, $interval, $yearEnd);
        $lastYearSuccess = [];
        $lastYearFailed = [];

        foreach ($lastYearRange as $date) {
            $lastYearSuccess[] = [
                $date->getTimestamp(),
                ApiTestRunner::find()
                    ->byProject($id)->byServer($server_id)
                    ->andWhere(['from_unixtime(start_at,\'%Y-%m\')' => $date->format('Y-m')])
                    ->byStatus(ApiTestRunner::STATUS_SUCCESS)
                    ->count()
            ];

            $lastYearFailed[] = [
                $date->getTimestamp(),
                ApiTestRunner::find()
                    ->byProject($id)->byServer($server_id)
                    ->andWhere(['from_unixtime(start_at,\'%Y-%m\')' => $date->format('Y-m')])
                    ->byStatus(ApiTestRunner::STATUS_FAILED)
                    ->count()
            ];
        }

        $lastYearDataset = [
            $lastYearSuccess,
            $lastYearFailed
        ];

        return $this->render('_graphs', [
            'totalForAllTime' => $totalForAllTime,
            'failedForAllTime' => $failedForAllTime,
            'successRatio' => $successRatio,
            'successfulForAllTime' => $successfulForAllTime,

            'lastWeekDataset' => $lastWeekDataset,
            'lastMonthDataset' => $lastMonthDataset,
            'lastYearDataset' => $lastYearDataset,

            'yearBegin' => $yearBegin,
            'monthBegin' => $monthBegin,
            'weekBegin' => $weekBegin,
            'yearEnd' => $yearEnd,
            'monthEnd' => $monthEnd,
            'weekEnd' => $weekEnd,
            'project' => $this->findProject($id),

            'filterModel' => $filterModel
        ]);
    }

    /**
     * Finds the ApiTestRunner model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ApiTestRunner the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ApiTestRunner::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function findProject($id)
    {
        $project = ApiTestProject::find()->my()->andWhere(['id' => $id])->one();
        if ( ! $project) {
            throw new NotFoundHttpException();
        }
        return $project;
    }
}
