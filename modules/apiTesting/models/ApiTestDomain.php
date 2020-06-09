<?php

namespace app\modules\apiTesting\models;

use Yii;

/**
 * This is the model class for table "api_test_server_domain".
 *
 * @property int $id
 * @property string $domain
 * @property int $status
 * @property int $txt_checked_at
 * @property int $txt
 * @property int $project-id
 * @property ApiTestServer[] $apiTestServers
 */
class ApiTestDomain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_test_domain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain', 'project_id'], 'required'],
            [['status', 'project_id'], 'integer'],
            [['domain'], 'string', 'max' => 255],
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
