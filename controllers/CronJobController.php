<?php

namespace app\controllers;

use app\models\search\CronJobSearch;
use app\repositories\CronJobRepository;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class CronJobController
 *
 * @package app\controllers
 */
class CronJobController extends Controller
{
    public CronJobRepository $cronJobRepository;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->cronJobRepository = new CronJobRepository();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * Starter page
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $searchModel = new CronJobSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'jobs'         => $this->cronJobRepository->findAllCronJob(),
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'jobId'        => null,
        ]);
    }

    /**
     * Filtered page
     *
     * @param int $id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $searchModel = new CronJobSearch();
        $searchModel->cron_job_id = $id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'jobs'         => $this->cronJobRepository->findAllCronJob(),
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'jobId'        => $id,
        ]);
    }
}
