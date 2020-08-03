<?php
/**
 * Created by PhpStorm.
 * User: manhd
 * Date: 02/08/2020
 * Time: 22:00
 */

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\helpers\Number;
use app\models\Currency;
use app\models\CurrencyRate;
use yii\base\Exception;
use yii\httpclient\Client;

class CurrencyRatesParserController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    const UPDATE_INTERVAL = 1 * 60 * 60; // seconds

    //call fixer api return data
    //
    //
    public function actionIndex()
    {
        $this->parser();
    }

    protected function parser()
    {
        $updatesCount = 0;

        $baseURL = \Yii::$app->params['currencyRateAPI'];
        $endpoint = 'latest';
        $accessKey = \Yii::$app->params['currencyRateToken'];
        $currencyBase = Currency::findOne(\Yii::$app->params['currencyRateBase']);

        $client = new Client(['baseUrl' => $baseURL . '/' . $accessKey . '/' . $endpoint . '/' . $currencyBase->code]);
        $response = $client->createRequest()
            ->addHeaders(['content-type' => 'application/json'])
            ->send();
        try {
            $data = $response->getData();
            if ('success' === $data['result']) {
                $exchangeRates = $data['conversion_rates'];
                foreach (array_keys($exchangeRates) as $key) {
                    $currency = Currency::findOne($key);
                    if (isset($currency)) {
                        $currencyRate = CurrencyRate::find()->where('from_currency_id=:from_currency_id && to_currency_id=:to_currency_id', [
                            ':from_currency_id' => $currencyBase->id,
                            ':to_currency_id' => $currency->id
                        ]);
                        if (!isset($currencyRate)) {
                            $currencyRate = new CurrencyRate();
                            $currencyRate->from_currency_id = $currencyBase->id;
                            $currencyRate->to_currency_id = $currency->id;
                        }
                        $currencyRate->rate = $exchangeRates[$key];
                        $currencyRate->updated_at = $data['time_last_update_unix'];
                        $currencyRate->save();
                        $updatesCount++;
                    }
                }
            }

            if ($updatesCount) {
                $this->output('Currencies parsed: ' . $updatesCount);
            }
        } catch (Exception $e) {
            echo 'ERROR: parsing result ' . $accessKey . ': ' . $e->getMessage() . "\n";
        }
    }
}
