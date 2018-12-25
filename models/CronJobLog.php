<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cron_job_log".
 *
 * @property int $id
 * @property string $message
 * @property int $cron_job_id
 * @property int $created_at
 *
 * @property CronJob $cronJob
 */
class CronJobLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cron_job_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message', 'cron_job_id'], 'required'],
            [['cron_job_id', 'created_at'], 'integer'],
            [['message'], 'string', 'max' => 255],
            [['cron_job_id'], 'exist', 'skipOnError' => true,
                'targetClass' => CronJob::className(),
                'targetAttribute' => ['cron_job_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert) {

        if ($this->isNewRecord) {
            $this->created_at = time();
        }

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'message' => Yii::t('app', 'Message'),
            'cron_job_id' => Yii::t('app', 'Cron Job ID'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCronJob()
    {
        return $this->hasOne(CronJob::className(), ['id' => 'cron_job_id']);
    }
}
