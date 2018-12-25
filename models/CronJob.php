<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "cron_job".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property CronJobLog[] $cronJobLogs
 */
class CronJob extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cron_job';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'status'], 'required'],
            [['description'], 'string'],
            [['status', 'created_at', 'updated_at', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 127],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCronJobLogs()
    {
        return $this->hasMany(CronJobLog::className(), ['cron_job_id' => 'id']);
    }
}
