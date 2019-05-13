<?php

namespace app\controllers;

use app\models\CronJob;
use app\models\search\CronJobSearch;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Class CronJobsController
 *
 * @package app\controllers
 */
class CronJobController extends Controller
{

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
            'jobs'         => $this->findJobModel(),
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
            'jobs'         => $this->findJobModel(),
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'jobId'        => $id,
        ]);
    }

    /**
     * Finds the model.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @return CronJob the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findJobModel()
    {
        if (($model = CronJob::findAll([1 => 1])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
