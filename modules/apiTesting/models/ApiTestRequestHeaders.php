<?php

namespace app\modules\apiTesting\models;

use Yii;

/**
 * This is the model class for table "api_test_request_headers".
 *
 * @property int $id
 * @property int $request_id
 * @property string|null $key
 * @property string|null $value
 * @property string|null $description
 *
 * @property ApiTestRequest $request
 */
class ApiTestRequestHeaders extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_request_headers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_id'], 'required'],
            [['request_id'], 'integer'],
            [['key', 'value', 'description'], 'string', 'max' => 255],
            [['request_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApiTestRequest::className(), 'targetAttribute' => ['request_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'request_id' => 'Request ID',
            'key' => 'Key',
            'value' => 'Value',
            'description' => 'Description',
        ];
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
     * @return ApiTestRequestHeadersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestRequestHeadersQuery(get_called_class());
    }
}
