<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\Croupier;
use app\models\StellarCroupier;
use app\models\StellarCroupierData;
use app\models\StellarServer;
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
        $this->sendGameProfits();
    }

    public function actionProduceBets(string $sourcePublicKey, string $sourcePrivateKey, float $amount, int $count = 10)
    {
        $stellarServer = new StellarServer();
        for ($i = 0; $i < $count; $i++) {
            $response = $stellarServer
                ->buildTransaction($sourcePublicKey)
                ->addLumenPayment(StellarServer::getCroupierPublicKey(), $amount)
                ->submit($sourcePrivateKey);
            if ($response->getResult()->succeeded()) {
                $this->output('Send XLM ' . number_format($amount, 6) . 'to Croupier from source');
            }
        }
    }

    /**
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
    protected function sendGameProfits()
    {
        $stellarServer = new StellarCroupier();

        $sinceCursor = StellarCroupierData::getLastPagingToken();

        $winners_count = 0;
        $bets = $stellarServer->getBets($sinceCursor);
        foreach ($bets as [
                 'player_public_key' => $playerPublicKey,
                 'bet_amount' => $betAmount,
                 'paging_token' => $pagingToken,
        ]) {
            if ($result = Croupier::prizeAmount($betAmount, $stellarServer->getCroupierBalance())) {
                if ($stellarServer->isTestnet()) {
                    $prizeAmount = 0.01;
                } else {
                    $prizeAmount = $result['prize_amount'];
                }

                $stellarServer->sendPrize($playerPublicKey, $prizeAmount, $result['winner_rate']);
                $this->output('[Croupier] User with account ' . $playerPublicKey . 'has won XLM ' . number_format($prizeAmount, 6) . 'with rate x' . $result['winner_rate']);
                $winners_count++;
            }

            StellarCroupierData::setLastPagingToken($pagingToken);
        }
        $this->output('[Croupier] ' . $winners_count . ' users won out of ' . count($bets));
    }
}
