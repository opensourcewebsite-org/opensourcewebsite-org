<?php

namespace app\modules\apiTesting\models;

use Yii;

/**
 * This is the model class for table "api_test_job".
 *
 * @property int $id
 * @property int|null $server_id Server identity
 * @property string $name Name of job
 *
 * @property ApiTestServer $server
 * @property ApiTestJobRequest[] $apiTestJobRequests
 * @property ApiTestRequest[] $requests
 */
class ApiTestJob extends \yii\db\ActiveRecord
{
    public $requestIds;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_job';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['requestIds'], 'safe'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'server_id' => 'Server identity',
            'name' => 'Name of job',
        ];
    }

    /**
     * Gets query for [[ApiTestJobRequests]].
     *
     * @return \yii\db\ActiveQuery|ApiTestJobRequestQuery
     */
    public function getApiTestJobRequests()
    {
        return $this->hasMany(ApiTestJobRequest::className(), ['job_id' => 'id']);
    }

    /**
     * Gets query for [[Requests]].
     *
     * @return \yii\db\ActiveQuery|ApiTestRequestQuery
     */
    public function getRequests()
    {
        return $this->hasMany(ApiTestRequest::className(), ['id' => 'request_id'])->viaTable('api_test_job_request', ['job_id' => 'id']);
    }

    public function getSchedules()
    {
        return $this->hasMany(ApiTestJobSchedule::className(), ['job_id' => 'id']);
    }

    public function getRunners()
    {
        return $this->hasMany(ApiTestRunner::className(), ['job_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestJobQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestJobQuery(get_called_class());
    }

    public function getProject()
    {
        return $this->hasOne(ApiTestProject::className(), ['id' => 'project_id']);
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->requestIds = $this->getRequests()->select('id')->column();
    }
}
