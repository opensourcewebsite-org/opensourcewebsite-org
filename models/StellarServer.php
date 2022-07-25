<?php

namespace app\models;

use DateTime;
use Exception;
use Yii;
use ZuluCrypto\StellarSdk\Horizon\ApiClient;
use ZuluCrypto\StellarSdk\Model\Payment;
use ZuluCrypto\StellarSdk\Server;

class StellarServer extends Server
{
    public const TRANSACTION_LIMIT = 100;

    /**
     * StellarServer constructor. Creates either testnet server or public server connection depending on Yii config
     * @throws \Exception
     */
    public function __construct()
    {
        if (!isset(Yii::$app->params['stellar'])
            || (isset(Yii::$app->params['stellar']['testNet']) && Yii::$app->params['stellar']['testNet'])) {
            parent::__construct(ApiClient::newTestnetClient());
            $this->isTestnet = true;
        } else {
            parent::__construct(ApiClient::newPublicClient());
        }
    }

    public static function getIssuerPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['issuer_public_key'] ?? null;
    }

    public static function getDistributorPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['distributor_public_key'] ?? null;
    }

    public static function getOperatorPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['operator_public_key'] ?? null;
    }

    public static function getOperatorPrivateKey(): ?string
    {
        return Yii::$app->params['stellar']['operator_private_key'] ?? null;
    }

    public static function getCroupierPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['croupier_public_key'] ?? null;
    }

    public static function getCroupierPrivateKey(): ?string
    {
        return Yii::$app->params['stellar']['croupier_private_key'] ?? null;
    }

    public static function getGiverPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['giver_public_key'] ?? null;
    }

    public static function getGiverPrivateKey(): ?string
    {
        return Yii::$app->params['stellar']['giver_private_key'] ?? null;
    }

    public function isTestnet(): bool
    {
        return $this->isTestnet;
    }

    public function getLastLedger(): string
    {
        return $this->getLedgers(null, 'desc', 1)[0]->getSequence();
    }

    /**
     * @param string $sourceId
     * @param string $destinationId
     * @param int $timeLowerBound
     * @param int $timeUpperBound
     * @return bool
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
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
}
