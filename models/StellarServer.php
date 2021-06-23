<?php

namespace app\models;

use DateTime;
use ZuluCrypto\StellarSdk\Model\Payment;
use ZuluCrypto\StellarSdk\Server;

class StellarServer
{
    private Server $server;

    public function __construct(bool $useTestNet)
    {
        $this->server = $useTestNet ? Server::testNet() : Server::publicNet();
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
            $this->server->getAccount($sourceId)->getTransactions(),
            fn ($t) =>
                $t->getCreatedAt() >= $timeLowerBound
                && $t->getCreatedAt() <= $timeUpperBound
                && !empty(array_filter(
                    // NOTE limit=50
                    $t->getPayments(),
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
