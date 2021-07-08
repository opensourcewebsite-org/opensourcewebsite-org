<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\StellarServer;
use app\models\UserStellarIncome;
use DateInterval;
use DateTime;
use yii\console\Controller;
use ZuluCrypto\StellarSdk\XdrModel\Asset;

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
        $server = new StellarServer();

        $today = new DateTime('today');
        $paymentDate = $server->getNextPaymentDate();

        if ($paymentDate != $today) {
            return;
        }

        foreach (StellarServer::MINIMUM_BALANCES as $assetCode => $minimumBalance) {
            if (self::moneySentAlready($assetCode, $today)) {
                continue;
            }

            self::deleteIncomesData($assetCode, $today);

            // Collect and save all asset holders
            $asset = Asset::newCustomAsset($assetCode, StellarServer::getIssuerPublicKey());
            $holders = $server->getAssetHolders($assetCode, $minimumBalance);
            $incomes = array_map(function ($holder) use ($assetCode, $asset) {
                $income = new UserStellarIncome();
                $income->account_id = $holder->getAccountId();
                $income->asset_code = $assetCode;
                $income->income = StellarServer::incomeWeekly($holder->getCustomAssetBalanceValue($asset));
                $income->save();
                return $income;
            }, $holders);

            // Send income to asset holders
            $results = $server->sendIncomeToAssetHolders($assetCode, $holders);

            // Save info about when and how much value were sent
            $processed_at = time();
            $report = [];
            foreach (array_map(null, $incomes, $results) as [$income, $result]) {
                $income->processed_at = $processed_at;
                $income->result_code = $result;
                $income->save();
                if (!array_key_exists($result, $report)) {
                    $report[$result] = 0;
                }
                $report[$result] += $income->income;
            }

            // Report about how much value were sent with which result code
            foreach ($report as $resultCode => $sum) {
                $resultCode = strtoupper(empty($resultCode) ? 'success' : $resultCode);
                $sum = number_format($sum, 2);
                $this->output("$resultCode: $assetCode $sum");
            }
        }

        $server->setNextPaymentDate();
    }

    private static function moneySentAlready(string $assetCode, DateTime $date): bool
    {
        $date = $date->setTime(0, 0);
        $nextDay = $date->add(new DateInterval('P1D'));

        return UserStellarIncome::find()
            ->where([
                'asset_code' => $assetCode
            ])
            ->andWhere([
                'between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp()
            ])
            ->andWhere([
                'not', ['processed_at' => null]
            ])
            ->exists();
    }

    private static function deleteIncomesData(string $assetCode, DateTime $date): void
    {
        $date = $date->setTime(0, 0);
        $nextDay = $date->add(new DateInterval('P1D'));

        UserStellarIncome::deleteAll([
            'and',
            ['asset_code' => $assetCode],
            ['between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp()],
            ['processed_at' => null],
        ]);
    }
}
