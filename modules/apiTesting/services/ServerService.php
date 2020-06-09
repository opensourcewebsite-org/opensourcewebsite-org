<?php

namespace app\modules\apiTesting\services;

use app\modules\apiTesting\models\ApiTestDomain;
use app\modules\apiTesting\models\ApiTestServer;
use Yii;
use yii\base\Component;

class ServerService extends Component
{
    const TXT_KEY = 'api-testing-txt-record';

    public function flushTxtKey()
    {
        Yii::$app->session->remove(self::TXT_KEY);
    }

    public function generateTxtKey()
    {
        if ( ! $this->getLatestTxtKey()) {
            Yii::$app->session->set(self::TXT_KEY, 'opensourcewebsite-verification='.Yii::$app->security->generateRandomString(60));
        }
    }

    public function getLatestTxtKey()
    {
        return Yii::$app->session->get(self::TXT_KEY);
    }

    public function checkTxtOnServerAndVerify(ApiTestServer $model)
    {
        $records = dns_get_record($model->domain->domain, DNS_TXT);

        $model->domain->txt_checked_at = time();

        foreach ($records as $record) {
            if ($record['txt'] == $model->domain->txt) {
                $model->domain->status = $model::STATUS_VERIFIED;
                $model->domain->save();
            }
        }

        $model->save();

        return false;
    }

    public function createServer(ApiTestServer $server)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $server->domain_id = $this->createDomain($server)->id;
            if ($server->save()) {
                $this->flushTxtKey();
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
        }
    }

    public function verifyServer(ApiTestServer $server)
    {
    }

    private function createDomain(ApiTestServer $server): ApiTestDomain
    {
        $domain = new ApiTestDomain([
            'project_id' => $server->project_id,
            'domain' => $server->domainFormValue,
            'status' => ApiTestServer::STATUS_VERIFICATION_PROGRESS,
            'txt' => $this->getLatestTxtKey()
        ]);

        $domain->save();
        return $domain;
    }

    private function updateDomain(ApiTestServer $server, ApiTestDomain $domain)
    {
    }

    private function findDomain($domain):?ApiTestDomain
    {
        return ApiTestDomain::findOne(['domain' => $domain]);
    }
}
