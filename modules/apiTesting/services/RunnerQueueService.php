<?php

namespace app\modules\apiTesting\services;

use app\modules\apiTesting\models\ApiTestJob;
use app\modules\apiTesting\models\ApiTestJobSchedule;
use app\modules\apiTesting\models\ApiTestRequest;
use app\modules\apiTesting\models\ApiTestRunner;
use Yii;
use yii\base\Component;
use yii\db\Expression;

class RunnerQueueService extends Component
{
    public function addJobToQueue(ApiTestJob $job)
    {
        $model = new ApiTestRunner([
            'job_id' => $job->id,
            'status' => ApiTestRunner::STATUS_WAITING,
            'triggered_by' => Yii::$app->user->id,
            'start_at' => time()
        ]);

        $model->save();
    }

    public function addJobToQueueBySchedule(ApiTestJobSchedule $schedule)
    {
        $model = new ApiTestRunner([
            'job_id' => $schedule->job->id,
            'status' => ApiTestRunner::STATUS_WAITING,
            'triggered_by_schedule' => $schedule->id,
            'start_at' => time()
        ]);

        $model->save();
    }

    public function addRequestToQueue(ApiTestRequest $request)
    {
        $model = new ApiTestRunner([
            'request_id' => $request->id,
            'status' => ApiTestRunner::STATUS_WAITING,
            'triggered_by' => Yii::$app->user->id,
            'start_at' => time()
        ]);

        $model->save();
    }
}
