<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\StellarServer;
use app\models\UserStellarIncome;
use DateInterval;
use DateTime;
use yii\console\Controller;

/**
 * Class StellarController
 *
 * @package app\commands
 */
class StellarController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->sendDepositProfits();
    }

    protected function sendDepositProfits()
    {
        $stellarServer = new StellarServer();

        $today = new DateTime('today');

        if (!$stellarServer->isPaymentDate($today)) {
            return;
        }

        foreach (StellarServer::MINIMUM_BALANCES as $assetCode => $minimumBalance) {
            if (self::incomesSentAlready($assetCode, $today)) {
                continue;
            }

            self::deleteIncomesData($assetCode, $today);

            // Collect and save all asset holders
            $stellarServer->fetchAndSaveAssetHolders($assetCode, $minimumBalance);
            $holders = self::getAssetHolders($assetCode, $today);

            // Send incomes to asset holders
            $report = $stellarServer->sendIncomeToAssetHolders($assetCode, $holders);

            // Report about how much value were sent with which result code
            foreach ($report as $resultCode => ['accounts_count' => $accountsCount, 'income_sent' => $incomeSent]) {
                $resultCode = strtoupper(empty($resultCode) ? 'success' : $resultCode);
                $incomeSent = number_format($incomeSent, 2);
                $this->output("$accountsCount accounts processed with status $resultCode. Paid $assetCode $incomeSent");
            }
        }

        $stellarServer->setNextPaymentDate();
        $nextPaymentDate = $stellarServer->getNextPaymentDate()->format('Y-m-d');
        $this->output("Next Payment Date: $nextPaymentDate");
    }

    /**
     * @param string $assetCode
     * @param \DateTime $date
     * @return UserStellarIncome[]
     */
    private static function getAssetHolders(string $assetCode, DateTime $date): array
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        return UserStellarIncome::find()
            ->where([
                'asset_code' => $assetCode,
                'processed_at' => null,
            ])
            ->andWhere([
                'between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp(),
            ])
            ->all();
    }

    private static function incomesSentAlready(string $assetCode, DateTime $date): bool
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        return UserStellarIncome::find()
            ->where([
                'asset_code' => $assetCode,
            ])
            ->andWhere([
                'between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp(),
            ])
            ->andWhere([
                'not', ['processed_at' => null],
            ])
            ->exists();
    }

    private static function deleteIncomesData(string $assetCode, DateTime $date): void
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        UserStellarIncome::deleteAll([
            'and',
            ['asset_code' => $assetCode],
            ['between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp()],
            ['processed_at' => null],
        ]);
    }
}
