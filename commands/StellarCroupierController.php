<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\StellarCroupier;
use yii\console\Controller;

/**
 * Class StellarCroupierController
 *
 * User sends some amount to croupier's account, then if they won, prize amount have being sent them back.
 *
 * @package app\commands
 */
class StellarCroupierController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->processBets();
    }

    /**
     * @param string $sourcePublicKey
     * @param string $sourcePrivateKey
     * @param float|null $amount
     * @param int|null $count
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
    public function actionGenerateBets(string $sourcePublicKey, string $sourcePrivateKey, float $amount = null, int $betCount = null)
    {
        $stellarServer = new StellarCroupier();

        if (!$stellarServer->isTestnet()) {
            $this->debug('ERROR: this action available only for Testnet');

            return;
        }

        if (!$betCount) {
            $betCount = rand(1, 10);
        }

        if (!$amount) {
            $amount = rand(1, 10000) / 1000;
        } else {
            $amount = max($amount, StellarCroupier::BET_MINIMUM_AMOUNT);
        }

        for ($betNumber = 1; $betNumber <= $betCount; $betNumber++) {
            $request = $stellarServer->buildTransaction($sourcePublicKey);
            $operationCount = rand(1, 3);
            for ($operationNumber = 1; $operationNumber <= $operationCount; $operationNumber++) {
                $request = $request->addLumenPayment(StellarCroupier::getCroupierPublicKey(), $amount);
            }

            $response = $request->submit($sourcePrivateKey);

            if ($response->getResult()->succeeded()) {
                $this->debug('Sent ' . $amount . ' XLM (Operations: ' . $operationCount . ') to Croupier (Bets: ' . $betNumber . '/' . $betCount . ')');
            } else {
                $this->debug('ERROR: failed to send ' . $amount . ' XLM (Operations: ' . $operationCount . ') to Croupier (Bets: ' . $betNumber . '/' . $betCount . ')');
            }
        }
    }

    /**
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
    protected function processBets()
    {
        $stellarServer = new StellarCroupier();

        ['bets_count' => $betsCount, 'wins' => $wins] = $stellarServer->processBets();

        if ($betsCount) {
            foreach ($wins as [
                'prize_amount' => $prizeAmount,
                'winner_rate' => $winnerRate,
            ]) {
                $this->output('Sent ' . $prizeAmount . ' XLM (x' . $winnerRate . ' Winner Prize)');
            }

            $this->output('Winners found: ' . count($wins) . '. Bets processed: ' . $betsCount);
        }
    }
}
