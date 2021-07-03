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
        $ASSETS = [
            'EUR' => 50,
            'USD' => 50,
            'THB' => 50,
            'RUB' => 50
        ];

        $weekDay = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ][4];

        $server = new StellarServer();

        $paymentDate = $server->getAccountDataByKey(StellarServer::getDistributorPublicKey(), 'next_payment_date');
        $today = new \DateTime('today');
        $nextWeekDay = new \DateTime('next ' . $weekDay);
        if (!isset($paymentDate)) {
            $paymentDate = $today->format('l') === $weekDay ? $today : $nextWeekDay;
        } else {
            $paymentDate = \DateTime::createFromFormat('Y-m-d|', $paymentDate);
        }
        if ($paymentDate !== $today) {
            if ($paymentDate < $today) {
                $paymentDate = $nextWeekDay;
            }
            $server
                ->buildTransaction(StellarServer::getDistributorPublicKey())
                ->setAccountData('next_payment_date', $paymentDate->format('Y-m-d'))
                ->submit(StellarServer::getOperatorPrivateKey());
            return;
        }

        foreach ($ASSETS as $assetCode => $minimumBalance) {
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
            }
        }

        $server
            ->buildTransaction(StellarServer::getDistributorPublicKey())
            ->setAccountData('next_payment_date', $nextWeekDay->format('Y-m-d'))
            ->submit(StellarServer::getOperatorPrivateKey());
    }
}
