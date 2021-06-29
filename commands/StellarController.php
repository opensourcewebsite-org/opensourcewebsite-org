<?php

namespace app\commands;

use app\models\StellarServer;
use app\models\UserStellarIncome;
use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use yii\console\Exception;
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
        $assetCodes = ['EUR', 'USD', 'THB', 'RUB'];
        $minimumBalance = 50;
        $server = new StellarServer();

        foreach ($assetCodes as $assetCode) {
            $asset = Asset::newCustomAsset($assetCode, StellarServer::getIssuerPublicKey());
            $holders = $server->getAssetHolders($assetCode, $minimumBalance);
            $created_at = time();
            $incomes = array_map(function ($holder) use ($created_at, $assetCode, $asset) {
                $income = new UserStellarIncome();
                $income->account_id = $holder->getAccountId();
                $income->asset_code = $assetCode;
                $income->balance = $holder->getCustomAssetBalanceValue($asset);
                $income->income = StellarServer::incomeWeekly($income->balance);
                $income->created_at = $created_at;
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
    }
}
