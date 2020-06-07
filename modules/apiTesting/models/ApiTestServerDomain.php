<?php

namespace app\modules\apiTesting\models;

use Yii;

/**
 * This is the model class for table "api_test_server_domain".
 *
 * @property int $id
 * @property string $domain
 * @property int $status
 *
 * @property ApiTestServer[] $apiTestServers
 */
class ApiTestServerDomain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_server_domain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain'], 'required'],
            [['status'], 'integer'],
            [['domain'], 'string', 'max' => 255],
            [['domain'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'domain' => 'Domain',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[ApiTestServers]].
     *
     * @return \yii\db\ActiveQuery|ApiTestServerQuery
     */
    public function getApiTestServers()
    {
        return $this->hasMany(ApiTestServer::className(), ['domain_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestServerDomainQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ApiTestServerDomainQuery(get_called_class());
    }
}
