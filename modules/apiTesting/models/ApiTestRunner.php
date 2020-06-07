<?php

namespace app\modules\apiTesting\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "api_test_runner".
 *
 * @property int $id
 * @property int|null $job_id Job identity
 * @property int|null $request_id Request identity
 * @property int|null $triggered_by User that triggered
 * @property int|null $timing Timing
 * @property int|null $status Run status
 * @property int|null $start_at Time when start
 * @property int|null $triggered_by_schedule
 * @property ApiTestJob $job
 * @property ApiTestRequest $request
 * @property User $user
 * @property ApiTestJobSchedule $schedule
 */
class ApiTestRunner extends \yii\db\ActiveRecord
{
    const STATUS_WAITING = 0;
    const STATUS_FAILED = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_SUCCESS = 3;

    public static function getStatusesList()
    {
        return [
            self::STATUS_WAITING => 'Waiting',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_IN_PROGRESS => 'In progress',
            self::STATUS_SUCCESS => 'Success',
        ];
    }

    public static function getStatusColorClasses()
    {
        return [
            self::STATUS_WAITING => 'warning',
            self::STATUS_FAILED => 'danger',
            self::STATUS_IN_PROGRESS => 'secondary',
            self::STATUS_SUCCESS => 'success',
        ];
    }

    public function getStatusColorClass()
    {
        return  $this::getStatusColorClasses()[$this->status];
    }

    public function getStatusLabel()
    {
        return $this::getStatusesList()[$this->status];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_runner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id', 'request_id', 'triggered_by_schedule', 'triggered_by', 'timing', 'status', 'start_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job identity',
            'request_id' => 'Request identity',
            'triggered_by' => 'User that triggered',
            'timing' => 'Timing',
            'status' => 'Run status',
            'start_at' => 'Time when start',
        ];
    }

    public function isJob()
    {
        return $this->job_id != null;
    }

    public function isRequest()
    {
        return $this->request_id != null;
    }

    /**
     * Gets query for [[Job]].
     *
     * @return \yii\db\ActiveQuery|ApiTestJobQuery
     */
    public function getJob()
    {
        return $this->hasOne(ApiTestJob::className(), ['id' => 'job_id']);
    }

    /**
     * Gets query for [[Request]].
     *
     * @return \yii\db\ActiveQuery|ApiTestRequestQuery
     */
    public function getRequest()
    {
        return $this->hasOne(ApiTestRequest::className(), ['id' => 'request_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'triggered_by']);
    }

    public function getSchedule()
    {
        return $this->hasOne(ApiTestJobSchedule::class, ['id' => 'triggered_by_schedule']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestRunnerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestRunnerQuery(get_called_class());
    }
}
