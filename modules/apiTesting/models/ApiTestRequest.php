<?php

namespace app\modules\apiTesting\models;

use app\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "api_test_request".
 *
 * @property int $id
 * @property int|null $server_id
 * @property string $name
 * @property string $method
 * @property string $uri
 * @property string|null $body
 * @property int $correct_response_code
 * @property int $updated_at
 * @property int $updated_by
 * @property string $content_type
 * @property string $expected_response_body
 *
 * @property ApiTestJobRequest[] $apiTestJobRequests
 * @property ApiTestRequestHeaders[] $apiTestRequestHeaders
 * @property ApiTestRequestLabel[] $apiTestRequestLabels
 * @property ApiTestJob[] $jobs
 * @property ApiTestLabel[] $labels
 * @property ApiTestServer $server
 * @property User $updatedBy
 * @property ApiTestResponse $latestResponse
 */
class ApiTestRequest extends \yii\db\ActiveRecord
{
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PUT = 'PUT';

    const CONTENT_TYPE_XML = 'application/JSON';
    const CONTENT_TYPE_JSON = 'text/HTML';
    const CONTENT_TYPE_HTML = 'text/XML';

    const EDITOR_VIEW_TYPE_RAW = 'rawinput';
    const EDITOR_VIEW_TYPE_HTML = 'html';
    const EDITOR_VIEW_TYPE_JSON = 'json';

    public $headers = [];
    public $labelIds = [];

    public static function getMethodsList()
    {
        return [
            self::METHOD_POST => self::METHOD_POST,
            self::METHOD_GET => self::METHOD_GET,
            self::METHOD_PATCH => self::METHOD_PATCH,
            self::METHOD_DELETE => self::METHOD_DELETE,
            self::METHOD_PUT => self::METHOD_PUT
        ];
    }

    public static function getContentTypesList()
    {
        return [
            self::CONTENT_TYPE_XML => self::CONTENT_TYPE_XML,
            self::CONTENT_TYPE_JSON => self::CONTENT_TYPE_JSON,
            self::CONTENT_TYPE_HTML => self::CONTENT_TYPE_HTML
        ];
    }

    public static function getEditorTypesList()
    {
        return [
            self::EDITOR_VIEW_TYPE_RAW => self::EDITOR_VIEW_TYPE_RAW,
            self::EDITOR_VIEW_TYPE_HTML => self::EDITOR_VIEW_TYPE_HTML,
            self::EDITOR_VIEW_TYPE_JSON => self::EDITOR_VIEW_TYPE_JSON
        ];
    }

    public function getFullUrl()
    {
        return $this->server->getFullAddress().$this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['server_id', 'correct_response_code', 'updated_at', 'updated_by'], 'integer'],
            [['name', 'method', 'uri', 'updated_by'], 'required'],
            [['body', 'expected_response_body'], 'string'],
            [['headers', 'labelIds'], 'safe'],
            [['name', 'method', 'uri', 'content_type'], 'string', 'max' => 255],
            [['server_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApiTestServer::className(), 'targetAttribute' => ['server_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
                'updatedAtAttribute' => 'updated_at'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'server_id' => 'Server ID',
            'name' => 'Name',
            'method' => 'Method',
            'uri' => 'Uri',
            'body' => 'Body',

            'correct_response_code' => 'Correct Response Code',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[ApiTestJobRequests]].
     *
     * @return \yii\db\ActiveQuery|ApiTestJobRequestQuery
     */
    public function getApiTestJobRequests()
    {
        return $this->hasMany(ApiTestJobRequest::className(), ['request_id' => 'id']);
    }

    /**
     * Gets query for [[ApiTestRequestHeaders]].
     *
     * @return \yii\db\ActiveQuery|ApiTestRequestHeadersQuery
     */
    public function getApiTestRequestHeaders()
    {
        return $this->hasMany(ApiTestRequestHeaders::className(), ['request_id' => 'id']);
    }

    /**
     * Gets query for [[ApiTestRequestLabels]].
     *
     * @return \yii\db\ActiveQuery|ApiTestRequestLabelQuery
     */
    public function getApiTestRequestLabels()
    {
        return $this->hasMany(ApiTestRequestLabel::className(), ['request_id' => 'id']);
    }

    /**
     * Gets query for [[Jobs]].
     *
     * @return \yii\db\ActiveQuery|ApiTestJobQuery
     */
    public function getJobs()
    {
        return $this->hasMany(ApiTestJob::className(), ['id' => 'job_id'])->viaTable('api_test_job_request', ['request_id' => 'id']);
    }

    /**
     * Gets query for [[Labels]].
     *
     * @return \yii\db\ActiveQuery|ApiTestLabelQuery
     */
    public function getLabels()
    {
        return $this->hasMany(ApiTestLabel::className(), ['id' => 'label_id'])->viaTable('api_test_request_label', ['request_id' => 'id']);
    }

    /**
     * Gets query for [[Server]].
     *
     * @return \yii\db\ActiveQuery|ApiTestServerQuery
     */
    public function getServer()
    {
        return $this->hasOne(ApiTestServer::className(), ['id' => 'server_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestRequestQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestRequestQuery(get_called_class());
    }

    public function afterFind()
    {
        parent::afterFind(); // TODO: Change the autogenerated stub
        $this->loadHeaders();
        $this->loadLabels();
    }

    public function getResponses()
    {
        return $this->hasMany(ApiTestResponse::className(), ['request_id' => 'id']);
    }

    public function getLatestResponse()
    {
        return $this->getResponses()->orderBy('created_at DESC')->one();
    }

    private function loadHeaders()
    {
        foreach ($this->apiTestRequestHeaders as $header) {
            $this->headers[] = [
                'description' => $header->description,
                'key' => $header->key,
                'value' => $header->value
            ];
        }
    }

    private function loadLabels()
    {
        $this->labelIds = $this->getLabels()->select('id')->column();
    }
}
