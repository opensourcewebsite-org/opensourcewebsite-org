<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\StellarOperator;
use DateTime;
use yii\console\Controller;

/**
 * Class StellarOperatorController
 *
 * @package app\commands
 */
class StellarOperatorController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->sendDepositProfits();
    }

    protected function sendDepositProfits()
    {
        if ($stellarServer = new StellarOperator()) {
            $today = new DateTime('today');

            if (!$stellarServer->isPaymentDate($today)) {
                return;
            }

            foreach (StellarOperator::MINIMUM_BALANCES as $assetCode => $minimumBalance) {
                if (!StellarOperator::incomesSentAlready($assetCode, $today)) {
                    // Delete all unfinished incomes data
                    StellarOperator::deleteIncomesDataFromDatabase($assetCode, $today);

                    // Collect and save all asset holders
                    $stellarServer->fetchAndSaveAssetHolders($assetCode, $minimumBalance);
                }

                // Send incomes to asset holders
                $report = $stellarServer->sendIncomeToAssetHolders($assetCode, $today);

                // Report about how much value were sent with which result code
                foreach ($report as $resultCode => ['accounts_count' => $accountsCount, 'income_sent' => $incomeSent]) {
                    $resultCode = strtoupper(empty($resultCode) ? 'success' : $resultCode);
                    $incomeSent = number_format($incomeSent, 2);
                    $this->output($resultCode . ' Accounts processed: ' . $accountsCount . '. Paid: ' . $incomeSent . ' ' . $assetCode);
                }
            }

            $stellarServer->setNextPaymentDate();
            $nextPaymentDate = $stellarServer->getNextPaymentDate()->format('Y-m-d');
            $this->output('Next Payment Date: ' . $nextPaymentDate);
        }
    }
}
