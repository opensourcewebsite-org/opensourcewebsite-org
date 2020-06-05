<?php

namespace app\modules\apiTesting\models;

use app\modules\apiTesting\services\networking\Requester;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "api_test_server".
 *
 * @property int $id
 * @property int|null $project_id Link to project
 * @property string $protocol Server protocol (http/https)
 * @property string $domain Server domain
 * @property string|null $path Api path
 * @property string $txt TXT record
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int $status
 * @property int $txt_checked_at
 *
 * @property ApiTestProject $project
 * @property ApiTestLabel[] $labels
 * @property ApiTestRequest[] $apiTestRequests
 */
class ApiTestServer extends \yii\db\ActiveRecord
{
    const STATUS_EXPIRED = 2;
    const STATUS_VERIFIED = 1;
    const STATUS_VERIFICATION_PROGRESS = 0;

    const PROTOCOL_HTTP = 'http://';
    const PROTOCOL_HTTPS = 'https://';

    public static function getProtocolList()
    {
        return [
            self::PROTOCOL_HTTPS => self::PROTOCOL_HTTPS,
            self::PROTOCOL_HTTP => self::PROTOCOL_HTTP
        ];
    }

    public function getFullAddress()
    {
        return $this->protocol.$this->domain.'/'.($this->path ? $this->path.'/' : '');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_server';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'created_at', 'updated_at', 'status', 'txt_checked_at'], 'integer'],
            [['protocol', 'domain', 'txt', 'status'], 'required'],
            [['protocol'], 'string', 'max' => 10],
            [['domain', 'path', 'txt'], 'string', 'max' => 255],
            [['domain'], 'unique'],
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
            'project_id' => 'Link to project',
            'protocol' => 'Server protocol (http/https)',
            'domain' => 'Server domain',
            'path' => 'Api path',
            'txt' => 'TXT record',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status'
            
        ];
    }

    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery|ApiTestProjectQuery
     */
    public function getProject()
    {
        return $this->hasOne(ApiTestProject::className(), ['id' => 'project_id']);
    }

    public function getLabels()
    {
        return $this->hasMany(ApiTestLabel::className(), ['server_id' => 'id']);
    }

    /**
     * Gets query for [[ApiTestJobs]].
     *
     * @return \yii\db\ActiveQuery|ApiTestJobQuery
     */
    public function getApiTestJobs()
    {
        return $this->hasMany(ApiTestJob::className(), ['server_id' => 'id']);
    }

    /**
     * Gets query for [[ApiTestRequests]].
     *
     * @return \yii\db\ActiveQuery|ApiTestRequestQuery
     */
    public function getApiTestRequests()
    {
        return $this->hasMany(ApiTestRequest::className(), ['server_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestServerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestServerQuery(get_called_class());
    }
}
