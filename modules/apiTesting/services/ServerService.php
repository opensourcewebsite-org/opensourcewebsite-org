<?php

namespace app\modules\apiTesting\services;

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
        $records = dns_get_record($model->domain, DNS_TXT);

        $model->txt_checked_at = time();

        foreach ($records as $record) {
            if ($record['txt'] == $model->txt) {
                $model->status = $model::STATUS_VERIFIED;
                $model->save();

                if ($exsistedDomain = ApiTestServer::find()->andWhere([
                    'domain' => $model->domain,
                ])->andWhere([
                    '!=', 'txt', $model->txt
                ])->one()) {
                    $exsistedDomain->status = $exsistedDomain::STATUS_EXPIRED;
                    $exsistedDomain->save();
                }
            }
        }

        $model->save();

        return false;
    }

    public function createServer(ApiTestServer $server)
    {
        $server->status = $server::STATUS_VERIFICATION_PROGRESS;
        if ($server->save()) {
            $this->flushTxtKey();
        }
    }

    public function verifyServer(ApiTestServer $server)
    {
    }
}
