<?php

namespace app\modules\apiTesting\services;

use app\modules\apiTesting\models\ApiTestJob;
use app\modules\apiTesting\models\ApiTestJobRequest;
use app\modules\apiTesting\models\ApiTestJobSchedule;
use app\modules\apiTesting\models\ApiTestRequest;
use app\modules\apiTesting\models\ApiTestRunner;
use yii\base\Component;

class JobService extends Component
{
    public function update(ApiTestJob $model)
    {
        $this->updateRequests($model);
        return $model->save();
    }

    public function addSchedule(ApiTestJob $job, ApiTestJobSchedule $schedule)
    {
        $job->link('schedules', $schedule);

        if ($schedule->custom_schedule_from_date && $schedule->custom_schedule_end_date) {
            $schedule->custom_schedule_from_date = strtotime($schedule->custom_schedule_from_date);
            $schedule->custom_schedule_end_date = strtotime($schedule->custom_schedule_end_date);
        }

        $result = $schedule->save();
        if ($schedule->custom_schedule_from_date && $schedule->custom_schedule_end_date) {
            $schedule->custom_schedule_end_date = date('m/d//Y', $schedule->custom_schedule_end_date);
            $schedule->custom_schedule_from_date = date('m/d/Y', $schedule->custom_schedule_from_date);
        }

        return $result;
    }

    private function updateRequests(ApiTestJob $model)
    {
        $this->flushRequests($model);
        $this->saveRequests($model);
    }

    private function saveRequests(ApiTestJob $model)
    {
        $requests = ApiTestRequest::findAll($model->requestIds);
        foreach ($requests as $request) {
            $model->link('requests', $request);
        }
    }

    private function flushRequests(ApiTestJob $model)
    {
        ApiTestJobRequest::deleteAll(['job_id' => $model->id]);
    }
}
