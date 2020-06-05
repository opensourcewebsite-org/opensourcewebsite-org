<?php

namespace app\modules\apiTesting\models;

use Yii;

/**
 * This is the model class for table "api_test_job_request".
 *
 * @property int $job_id Job identity
 * @property int $request_id Request identity
 *
 * @property ApiTestJob $job
 * @property ApiTestRequest $request
 */
class ApiTestJobRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_job_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id', 'request_id'], 'required'],
            [['job_id', 'request_id'], 'integer'],
            [['job_id', 'request_id'], 'unique', 'targetAttribute' => ['job_id', 'request_id']],
            [['job_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApiTestJob::className(), 'targetAttribute' => ['job_id' => 'id']],
            [['request_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApiTestRequest::className(), 'targetAttribute' => ['request_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'job_id' => 'Job identity',
            'request_id' => 'Request identity',
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

    /**
     * Gets query for [[Request]].
     *
     * @return \yii\db\ActiveQuery|ApiTestRequestQuery
     */
    public function getRequest()
    {
        return $this->hasOne(ApiTestRequest::className(), ['id' => 'request_id']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestJobRequestQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestJobRequestQuery(get_called_class());
    }
}
