<?php

namespace app\modules\apiTesting\models;

use Yii;

/**
 * This is the model class for table "api_test_job_schedule".
 *
 * @property int $id
 * @property int $job_id Job identity
 * @property int $status Is Active
 * @property int $schedule_periodicity
 * @property int|null $custom_schedule_from_date Date for schedule start
 * @property int|null $custom_schedule_end_date Date for schedule stop
 * @property string $description Description for job
 *
 * @property ApiTestJob $job
 */
class ApiTestJobSchedule extends \yii\db\ActiveRecord
{
    const PERIODICITY_EVERYDAY = 0;
    const PERIODICITY_EVERY_WEEK = 1;
    const PERIODICITY_EVERY_MONTH = 2;
    const PERIODICITY_CUSTOM = 4;

    public static function getPeriodicityList()
    {
        return [
            self::PERIODICITY_EVERYDAY => 'Everyday',
            self::PERIODICITY_EVERY_WEEK => 'Every week',
            self::PERIODICITY_EVERY_MONTH => 'Every month',
            self::PERIODICITY_CUSTOM => 'Custom'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_job_schedule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id', 'schedule_periodicity', 'description'], 'required'],
            [['job_id', 'status', 'schedule_periodicity', 'custom_schedule_from_date', 'custom_schedule_end_date'], 'integer'],
            [['description'], 'string', 'max' => 255],
            [['job_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApiTestJob::className(), 'targetAttribute' => ['job_id' => 'id']],
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
            'status' => 'Is Active',
            'schedule_periodicity' => 'Schedule Periodicity',
            'custom_schedule_from_date' => 'Date for schedule start',
            'custom_schedule_end_date' => 'Date for schedule stop',
            'description' => 'Description for job',
        ];
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

    public function getRunners()
    {
        return $this->hasMany(ApiTestRunner::className(), ['job_id' => 'job_id']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestJobScheduleQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestJobScheduleQuery(get_called_class());
    }
}
