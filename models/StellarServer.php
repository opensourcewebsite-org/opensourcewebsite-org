<?php

namespace app\models;

use Yii;
use DateTime;
use ZuluCrypto\StellarSdk\Model\Payment;
use ZuluCrypto\StellarSdk\Server;

class StellarServer
{
    private Server $server;

    public function __construct()
    {
        if (!isset(Yii::$app->params['stellar'])) {
            throw new \Exception('No stellar params');
        }

        $this->server = isset(Yii::$app->params['stellar']['testNet']) && Yii::$app->params['stellar']['testNet'] ? Server::testNet() : Server::publicNet();
    }

    public function accountExists(string $accountId): bool
    {
        return $this->server->accountExists($accountId);
    }

    public function operationExists(string $sourceId, string $destinationId, int $timeLowerBound, int $timeUpperBound): bool
    {
        $timeLowerBound = (new DateTime())->setTimestamp($timeLowerBound);
        $timeUpperBound = (new DateTime())->setTimestamp($timeUpperBound);

        return !empty(array_filter(
            // NOTE limit=50
            $this->server->getAccount($sourceId)->getTransactions(null, 10, 'desc'),
            fn ($t) =>
                $t->getCreatedAt() >= $timeLowerBound
                && $t->getCreatedAt() <= $timeUpperBound
                && !empty(array_filter(
                    // NOTE limit=50
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

    public function getIssuerPublicKey()
    {
        return Yii::$app->params['stellar']['issuer_public_key'] ?? null;
    }

    public function getDistributorPublicKey()
    {
        return Yii::$app->params['stellar']['distributor_public_key'] ?? null;
    }

    public function getOperatorPublicKey()
    {
        return Yii::$app->params['stellar']['operator_public_key'] ?? null;
    }
}
