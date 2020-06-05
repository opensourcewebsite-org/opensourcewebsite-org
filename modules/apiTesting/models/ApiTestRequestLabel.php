<?php

namespace app\modules\apiTesting\models;

use Yii;

/**
 * This is the model class for table "api_test_request_label".
 *
 * @property int $label_id Label identity
 * @property int $request_id Request identity
 *
 * @property ApiTestLabel $label
 * @property ApiTestRequest $request
 */
class ApiTestRequestLabel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_request_label';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['label_id', 'request_id'], 'required'],
            [['label_id', 'request_id'], 'integer'],
            [['label_id', 'request_id'], 'unique', 'targetAttribute' => ['label_id', 'request_id']],
            [['label_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApiTestLabel::className(), 'targetAttribute' => ['label_id' => 'id']],
            [['request_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApiTestRequest::className(), 'targetAttribute' => ['request_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'label_id' => 'Label identity',
            'request_id' => 'Request identity',
        ];
    }

    /**
     * Gets query for [[Label]].
     *
     * @return \yii\db\ActiveQuery|ApiTestLabelQuery
     */
    public function getLabel()
    {
        return $this->hasOne(ApiTestLabel::className(), ['id' => 'label_id']);
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
     * @return ApiTestRequestLabelQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestRequestLabelQuery(get_called_class());
    }
}
