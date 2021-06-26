<?php

namespace app\models;

use Yii;
use DateTime;
use ZuluCrypto\StellarSdk\Model\Payment;
use ZuluCrypto\StellarSdk\Server;
use ZuluCrypto\StellarSdk\Horizon\ApiClient;

class StellarServer extends Server
{
    public function __construct()
    {
        if (!isset(Yii::$app->params['stellar'])) {
            throw new \Exception('No stellar params');
        }

        if (isset(Yii::$app->params['stellar']['testNet']) && Yii::$app->params['stellar']['testNet']) {
            parent::__construct(ApiClient::newTestnetClient());
            $this->isTestnet = true;
        } else {
            parent::__construct(ApiClient::newPublicClient());
        }
    }

    public function operationExists(string $sourceId, string $destinationId, int $timeLowerBound, int $timeUpperBound): bool
    {
        $timeLowerBound = (new DateTime())->setTimestamp($timeLowerBound);
        $timeUpperBound = (new DateTime())->setTimestamp($timeUpperBound);

        return !empty(array_filter(
            $this->getAccount($sourceId)->getTransactions(null, 10, 'desc'),
            fn ($t) =>
                $t->getCreatedAt() >= $timeLowerBound
                && $t->getCreatedAt() <= $timeUpperBound
                && !empty(array_filter(
                    $t->getPayments(null, 10, 'desc'),
                    fn ($p) =>
                        get_class($p) === Payment::class
                        && $p->isNativeAsset()
                        && $p->getAmount()->getBalance() > 0
                        && $p->getFromAccountId() === $sourceId
                        && $p->getToAccountId() === $destinationId
                ))
        ));
    }

    public function getIssuerPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['issuer_public_key'] ?? null;
    }

    public function getDistributorPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['distributor_public_key'] ?? null;
    }

    public function getOperatorPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['operator_public_key'] ?? null;
    }
}
