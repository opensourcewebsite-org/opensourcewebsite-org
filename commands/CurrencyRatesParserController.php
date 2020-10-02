<?php

namespace app\commands;

use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\helpers\Number;
use app\models\Currency;
use app\models\CurrencyRate;
use app\models\CronJob;
use yii\base\Exception;
use yii\httpclient\Client;

/**
 * Class CurrencyRatesParserController
 *
 * @package app\commands
 */
class CurrencyRatesParserController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    private $updatesCount = 0;
    private $baseURL = 'https://api.exchangeratesapi.io/';
    private $endpoint = 'latest';
    const UPDATE_INTERVAL = 12 * 60 * 60; // seconds

    public function actionIndex()
    {
        $this->parser();
    }

    protected function parser()
    {
        $cronJob = CronJob::find()
            ->where([
                CronJob::tableName() . '.name' => 'CurrencyRatesParser'
            ])
            ->one();

        if (!isset($cronJob)) {
            return;
        }

        if (($cronJob->created_at != $cronJob->updated_at) && ($cronJob->updated_at > (time() - self::UPDATE_INTERVAL))) {
            return;
        }

        $currencyBase = Currency::find()
            ->where([
                'code' => 'USD',
            ])
            ->one();

        $client = new Client([
            'baseUrl' => $this->baseURL . $this->endpoint,
        ]);

        $response = $client->createRequest()
            ->addHeaders([
                'content-type' => 'application/json',
            ])
            ->setData([
                'base' => 'USD',
            ])
            ->send();

        try {
            $data = $response->getData();

            if (count($data['rates']) > 0) {
                $exchangeRates = $data['rates'];
                foreach (array_keys($exchangeRates) as $key) {
                    $currency = Currency::find()
                        ->where([
                            'code' => $key,
                        ])
                        ->one();

                    if (isset($currency)) {
                        $currencyRate = CurrencyRate::find()
                            ->where([
                                'or',
                                ['updated_at' => null],
                                ['<', 'updated_at', time() - self::UPDATE_INTERVAL],
                            ])
                            ->andWhere([
                                'from_currency_id' => $currencyBase->id,
                                'to_currency_id' => $currency->id,
                            ])
                            ->one();

                        if (!isset($currencyRate)) {
                            $currencyRate = new CurrencyRate();
                            $currencyRate->from_currency_id = $currencyBase->id;
                            $currencyRate->to_currency_id = $currency->id;
                        }

                        $currencyRate->rate = $exchangeRates[$key];
                        $currencyRate->updated_at = time();
                        $currencyRate->save();

                        $this->updatesCount++;
                    }
                }
            }

            if ($this->updatesCount) {
                $this->output('Currency rates parsed: ' . $this->updatesCount);
            }
        } catch (Exception $e) {
            echo 'ERROR: parsing result from ' . $baseURL . ': ' . $e->getMessage() . "\n";
        }
    }
}
