<?php

namespace app\modules\apiTesting\services;

use app\modules\apiTesting\models\ApiTestServer;
use app\modules\apiTesting\models\ApiTestServerDomain;
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
        $records = dns_get_record($model->domain, DNS_TXT);

        $model->txt_checked_at = time();

        foreach ($records as $record) {
            if ($record['txt'] == $model->txt) {
                $model->domain->status = $model::STATUS_VERIFIED;
                $model->domain->save();
            }
        }

        $model->save();

        return false;
    }

    public function createServer(ApiTestServer $server)
    {
        if ($domain = $this->findDomain($server->domainFormValue)) {
            $server->domain_id = $domain->id;
        } else {
            $server->domain_id = $this->createDomain($server->domainFormValue)->id;
        }

        if ($server->save()) {
            $this->flushTxtKey();
        }
    }

    public function verifyServer(ApiTestServer $server)
    {
    }

    private function createDomain($domain): ApiTestServerDomain
    {
        $domain = new ApiTestServerDomain([
            'domain' => $domain,
            'status' => ApiTestServer::STATUS_VERIFICATION_PROGRESS
        ]);
        $domain->save();
        return $domain;
    }

    private function updateDomain(ApiTestServer $server, ApiTestServerDomain $domain)
    {
    }

    private function findDomain($domain):?ApiTestServerDomain
    {
        return ApiTestServerDomain::findOne(['domain' => $domain]);
    }
}
