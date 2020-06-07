<?php

namespace app\modules\apiTesting\models;

use function GuzzleHttp\Promise\all;
use Yii;

/**
 * This is the model class for table "api_test_label".
 *
 * @property int $id
 * @property int $project_id
 * @property string $name
 *
 * @property ApiTestRequestLabel[] $apiTestRequestLabels
 * @property ApiTestRequest[] $requests
 * @property ApiTestProject $project
 */
class ApiTestLabel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_label';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'name'], 'required'],
            [['project_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApiTestProject::className(), 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'project ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[ApiTestRequestLabels]].
     *
     * @return \yii\db\ActiveQuery|ApiTestRequestLabelQuery
     */
    public function getApiTestRequestLabels()
    {
        return $this->hasMany(ApiTestRequestLabel::className(), ['label_id' => 'id']);
    }

    /**
     * Gets query for [[Requests]].
     *
     * @return \yii\db\ActiveQuery|ApiTestRequestQuery
     */
    public function getRequests()
    {
        return $this->hasMany(ApiTestRequest::className(), ['id' => 'request_id'])->viaTable('api_test_request_label', ['label_id' => 'id']);
    }

    /**
     * Gets query for [[project]].
     *
     * @return \yii\db\ActiveQuery|ApiTestprojectQuery
     */
    public function getProject()
    {
        return $this->hasOne(ApiTestProject::className(), ['id' => 'project_id']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestLabelQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestLabelQuery(get_called_class());
    }
}
