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
        $this->sendGameProfits();
    }

    /**
     * @param string $sourcePublicKey
     * @param string $sourcePrivateKey
     * @param float $amount
     * @param int $count
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
    public function actionProduceBets(string $sourcePublicKey, string $sourcePrivateKey, float $amount, int $count = 10)
    {
        $stellarServer = new StellarCroupier();
        for ($i = 0; $i < $count; $i++) {
            if ($stellarServer->makeBet($sourcePublicKey, $sourcePrivateKey, $amount)) {
                $this->output('Sent XLM ' . number_format($amount, 6) . ' to Croupier from Source');
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

        ['bets_count' => $betsCount, 'wins' => $wins] = $stellarServer->sendPrizesToPlayers();

        foreach ($wins as [
            'player_public_key' => $playerPublicKey,
            'prize_amount' => $prizeAmount,
            'winner_rate' => $winnerRate,
        ]) {
            $this->output('[Croupier] User with account ' . $playerPublicKey . ' has won XLM ' . number_format($prizeAmount, 6) . ' with rate x' . $winnerRate);
        }
        $this->output('[Croupier] ' . count($wins) . ' users won out of ' . $betsCount);
    }
}
