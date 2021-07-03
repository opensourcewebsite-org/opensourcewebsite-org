<?php

namespace app\commands;

use app\models\StellarServer;
use app\models\UserStellarIncome;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
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
        $minimumBalances = StellarServer::MINIMUM_BALANCES;

        $server = new StellarServer();

        $today = new \DateTime('today');
        $paymentDate = $server->getNextPaymentDate();

        if ($paymentDate !== $today) {
            return;
        }

        $report = [];

        foreach ($minimumBalances as $assetCode => $minimumBalance) {
            $asset = Asset::newCustomAsset($assetCode, StellarServer::getIssuerPublicKey());
            $holders = $server->getAssetHolders($assetCode, $minimumBalance);
            $incomes = array_map(function ($holder) use ($assetCode, $asset) {
                $income = new UserStellarIncome();
                $income->account_id = $holder->getAccountId();
                $income->asset_code = $assetCode;
                $income->balance = $holder->getCustomAssetBalanceValue($asset);
                $income->income = StellarServer::incomeWeekly($income->balance);
                $income->save();
                return $income;
            }, $holders);
            $results = $server->sendIncomeToAssetHolders($assetCode, $holders);
            $processed_at = time();
            foreach (array_map(null, $incomes, $results) as [$income, $result]) {
                $income->processed_at = $processed_at;
                $income->result_code = $result;
                $income->save();
                $report[$assetCode][$result] += $income->income;
            }
        }

        foreach ($report as $assetCode => $results) {
            foreach ($results as $resultCode => $sum) {
                $resultCode = strtoupper($resultCode ?? 'success');
                $sum = number_format($sum, 2);
                $this->output("$resultCode: $assetCode $sum");
            }
        }

        $server->setNextPaymentDate();
    }
}
