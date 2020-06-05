<?php

namespace app\modules\apiTesting\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "api_test_response".
 *
 * @property int $id
 * @property int $request_id Request identity
 * @property string|null $headers Headers
 * @property string|null $body Body
 * @property string|null $cookies Cookies
 * @property int $code Response code
 * @property int|null $time Request execution time
 * @property int|null $size Size of response
 * @property int $created_at Response time
 *
 * @property ApiTestRequest $request
 */
class ApiTestResponse extends \yii\db\ActiveRecord
{
    public static function responseCodesList()
    {
        return [
            null => 'No Response',
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing', // WebDAV; RFC 2518
            103 => 'Early Hints', // RFC 8297
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information', // since HTTP/1.1
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content', // RFC 7233
            207 => 'Multi-Status', // WebDAV; RFC 4918
            208 => 'Already Reported', // WebDAV; RFC 5842
            226 => 'IM Used', // RFC 3229
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found', // Previously "Moved temporarily"
            303 => 'See Other', // since HTTP/1.1
            304 => 'Not Modified', // RFC 7232
            305 => 'Use Proxy', // since HTTP/1.1
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect', // since HTTP/1.1
            308 => 'Permanent Redirect', // RFC 7538
            400 => 'Bad Request',
            401 => 'Unauthorized', // RFC 7235
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required', // RFC 7235
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed', // RFC 7232
            413 => 'Payload Too Large', // RFC 7231
            414 => 'URI Too Long', // RFC 7231
            415 => 'Unsupported Media Type', // RFC 7231
            416 => 'Range Not Satisfiable', // RFC 7233
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot', // RFC 2324, RFC 7168
            421 => 'Misdirected Request', // RFC 7540
            422 => 'Unprocessable Entity', // WebDAV; RFC 4918
            423 => 'Locked', // WebDAV; RFC 4918
            424 => 'Failed Dependency', // WebDAV; RFC 4918
            425 => 'Too Early', // RFC 8470
            426 => 'Upgrade Required',
            428 => 'Precondition Required', // RFC 6585
            429 => 'Too Many Requests', // RFC 6585
            431 => 'Request Header Fields Too Large', // RFC 6585
            451 => 'Unavailable For Legal Reasons', // RFC 7725
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates', // RFC 2295
            507 => 'Insufficient Storage', // WebDAV; RFC 4918
            508 => 'Loop Detected', // WebDAV; RFC 5842
            510 => 'Not Extended', // RFC 2774
            511 => 'Network Authentication Required', // RFC 6585
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_response';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
                'createdAtAttribute' => 'created_at'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_id', 'code'], 'required'],
            [['request_id', 'code', 'time', 'size', 'created_at'], 'integer'],
            [['headers', 'body', 'cookies'], 'string'],
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
            'request_id' => 'Request identity',
            'headers' => 'Headers',
            'body' => 'Body',
            'cookies' => 'Cookies',
            'code' => 'Response code',
            'time' => 'Request execution time',
            'size' => 'Size of response',
            'created_at' => 'Response time',
        ];
    }

    /**
     * Gets query for [[Request]].
     *
     * @return \yii\db\ActiveQuery|ApiTestServerQuery
     */
    public function getRequest()
    {
        return $this->hasOne(ApiTestRequest::className(), ['id' => 'request_id']);
    }

    public function getJob()
    {
        return $this->hasOne(ApiTestJob::className(), ['id' => 'job_id']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestResponseQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestResponseQuery(get_called_class());
    }

    public function getStatusLabel()
    {
        if ($this->code == $this->request->correct_response_code) {
            return 'Success';
        } else {
            return 'Failed';
        }
    }

    public function getCodeFormatted()
    {
        return $this->code.' '.$this::responseCodesList()[$this->code];
    }

    public function codeTextStyle()
    {
        switch (floor($this->code / 100)):
            case 1:
                return 'warning';
        case 2:
                return 'success';
        case 3:
                return 'danger';
        case 4:
                return 'danger';
        case 5:
                return 'danger';
        default:
                return 'warning';
        endswitch;
    }
}
