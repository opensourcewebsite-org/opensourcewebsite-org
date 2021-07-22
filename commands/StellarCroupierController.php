<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\Croupier;
use app\models\StellarServer;
use yii\console\Controller;
use ZuluCrypto\StellarSdk\Model\Payment;

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

    public const PRIZE_MEMO_TEXT = 'x%d Winner Prize';

    public function actionIndex()
    {
        $this->sendGameProfits();
    }

    /**
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
    protected function sendGameProfits()
    {
        $stellarServer = new StellarServer();

        // TODO get sinceCursor
        $sinceCursor = null;

        $croupierAccount = $stellarServer->getAccount($stellarServer->getCroupierPublicKey());

        $payments = array_filter(
            $croupierAccount->getPayments($sinceCursor), // gets at most 50 payments
            fn ($p) =>
                get_class($p) === Payment::class
                && $p->getToAccountId() == StellarServer::getCroupierPublicKey()
                && $p->isNativeAsset()
        );

        $croupierBalance = $croupierAccount->getNativeBalance();
        $winners_count = 0;
        /** @var Payment $payment */
        foreach ($payments as $payment) {
            $playerPublicKey = $payment->getFromAccountId();
            $betAmount = $payment->getAmount()->getBalance();
            if ($result = Croupier::prizeAmount($betAmount, $croupierBalance)) {
                if ($stellarServer->isTestnet()) {
                    $prizeAmount = 0.01;
                } else {
                    $prizeAmount = $result['prize_amount'];
                }
                $response = $stellarServer
                    ->buildTransaction(StellarServer::getCroupierPublicKey())
                    ->addLumenPayment($playerPublicKey, $prizeAmount)
                    ->setTextMemo(sprintf(self::PRIZE_MEMO_TEXT, $result['winner_rate']))
                    ->submit(StellarServer::getCroupierPrivateKey());
                $croupierBalance -= $prizeAmount + $response->getResult()->getFeeCharged()->getScaledValue();

                $this->output('[Croupier] User with account ' . $playerPublicKey . 'has won XLM ' . number_format($prizeAmount, 6) . 'with rate x' . $result['winner_rate']);
                $winners_count++;
            }

            // TODO save $payment->getPagingToken()
        }
        $this->output('[Croupier] ' . $winners_count . ' users won out of ' . count($payments));
    }
}
