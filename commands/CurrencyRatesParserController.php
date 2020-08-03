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

    public function actionIndex()
    {
        $this->parser();
    }

    protected function parser()
    {
        $updatesCount = 0;

        $baseURL = 'https://api.exchangeratesapi.io/';
        $endpoint = 'latest';
        $currencyBase = Currency::find()->where('code=:code', [
            ':code' => 'USD'
        ])->one();
        $client = new Client(['baseUrl' => $baseURL . $endpoint]);
        $response = $client->createRequest()
            ->addHeaders(['content-type' => 'application/json'])
            ->setData([
                'base' => 'USD',
            ])->send();
        try {
            $data = $response->getData();
            if (count($data['rates']) > 0) {
                $exchangeRates = $data['rates'];
                foreach (array_keys($exchangeRates) as $key) {
                    $currency = Currency::find()->where('code=:code', [
                        ':code' => $key
                    ])->one();
                    if (isset($currency)) {
                        $currencyRate = CurrencyRate::find()->where('from_currency_id=:from_currency_id && to_currency_id=:to_currency_id', [
                            ':from_currency_id' => $currencyBase->id,
                            ':to_currency_id' => $currency->id
                        ])->one();
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
            echo 'ERROR: parsing result from ' . $baseURL . ': ' . $e->getMessage() . "\n";
        }
    }
}
