<?php

namespace app\modules\apiTesting\models;

use app\modules\apiTesting\services\RunnerScheduleManager;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[ApiTestJobSchedule]].
 *
 * @see ApiTestJobSchedule
 */
class ApiTestJobScheduleQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestJobSchedule[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestJobSchedule|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function activeByCustomSchedule()
    {
        /* Получим запланированиое на сегодня */
        $scheduledIds = RunnerScheduleManager::getScheduleIdsThatAlreadyInRunnerByInterval(RunnerScheduleManager::INTERVAL_TODAY);

        return $this
            ->joinWith('runners r')
            ->andWhere([
                'AND',
                ['NOT IN', ApiTestJobSchedule::tableName().'.id', $scheduledIds],
                ['schedule_periodicity' => ApiTestJobSchedule::PERIODICITY_CUSTOM],
                ['<=', 'custom_schedule_from_date', time()],
                ['>=', 'custom_schedule_end_date', time()],
            ])
            ->andWhere([ApiTestJobSchedule::tableName().'.status' => 1]);
    }

    public function activeByEverydaySchedule()
    {
        /* Получим запланированиое на сегодня */
        $scheduledIds = RunnerScheduleManager::getScheduleIdsThatAlreadyInRunnerByInterval(RunnerScheduleManager::INTERVAL_TODAY);

        return $this
            ->joinWith('runners r')
            ->andWhere([
                'AND',
                ['NOT IN', ApiTestJobSchedule::tableName().'.id', $scheduledIds],
                ['schedule_periodicity' => ApiTestJobSchedule::PERIODICITY_EVERYDAY]
            ])
            ->andWhere([ApiTestJobSchedule::tableName().'.status' => 1]);
    }

    public function activeByEveryWeekSchedule()
    {
        /* Получим запланированиое на сегодня */
        $scheduledIds = RunnerScheduleManager::getScheduleIdsThatAlreadyInRunnerByInterval(RunnerScheduleManager::INTERVAL_WEEK);

        return $this
            ->joinWith('runners r')
            ->andWhere([
                'AND',
                ['NOT IN', ApiTestJobSchedule::tableName().'.id', $scheduledIds],
                ['schedule_periodicity' => ApiTestJobSchedule::PERIODICITY_EVERY_WEEK]
            ])
            ->andWhere([ApiTestJobSchedule::tableName().'.status' => 1]);
    }

    public function activeByEveryMonthSchedule()
    {
        /* Получим запланированиое на сегодня */
        $scheduledIds = RunnerScheduleManager::getScheduleIdsThatAlreadyInRunnerByInterval(RunnerScheduleManager::INTERVAL_MONTH);

        return $this
            ->joinWith('runners r')
            ->andWhere([
                'AND',
                ['NOT IN', ApiTestJobSchedule::tableName().'.id', $scheduledIds],
                ['schedule_periodicity' => ApiTestJobSchedule::PERIODICITY_EVERY_MONTH]
            ])
            ->andWhere([ApiTestJobSchedule::tableName().'.status' => 1]);
    }
}
