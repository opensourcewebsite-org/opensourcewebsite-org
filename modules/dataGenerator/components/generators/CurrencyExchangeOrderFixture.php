<?php

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;
use app\models\User;
use app\modules\dataGenerator\helpers\LatLonHelper;
use app\services\CurrencyExchangeService;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\Console;

class CurrencyExchangeOrderFixture extends ARGenerator
{
    private CurrencyExchangeService $service;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->service = new CurrencyExchangeService();
    }

    /**
     * @throws ARGeneratorException
     */
    public function init()
    {
        if (!Currency::find()->exists()) {
            throw new ARGeneratorException('Impossible to create Exchange Order - there are no Currency in DB!');
        }

        parent::init();
    }

    protected function factoryModel(): ?ActiveRecord
    {
        $user = $this->findUser();

        [$sellCurrencyId, $buyCurrencyId] = $this->getRandCurrenciesPair();

        if (!$user || !$sellCurrencyId || !$buyCurrencyId) {
            return null;
        }

        $londonCenter = [51.509865, -0.118092];
        [$orderSellingLat, $orderSellingLon] = LatLonHelper::generateRandomPoint($londonCenter, 100);
        [$orderBuyingLat, $orderBuyingLon] = LatLonHelper::generateRandomPoint($londonCenter, 200);

        $crossRateOn = (int)static::getFaker()->boolean();
        $sellingCashOn = (int)static::getFaker()->boolean();
        $buyingCashOn = (int)static::getFaker()->boolean();

        $sellingPaymentMethodsIds = $this->getPaymentMethodsIds($sellCurrencyId);
        $buyingPaymentMethodsIds = $this->getPaymentMethodsIds($buyCurrencyId);

        if ($sellingPaymentMethodsIds) {
            $orderSellingPaymentMethodsIds = static::getFaker()->randomElements(
                $sellingPaymentMethodsIds,
                static::getFaker()->numberBetween(1, count($sellingPaymentMethodsIds))
            );
        } else {
            $orderSellingPaymentMethodsIds = [];
            $sellingCashOn = CurrencyExchangeOrder::CASH_ON;
        }

        if ($buyingPaymentMethodsIds) {
            $orderBuyingPaymentMethodsIds = static::getFaker()->randomElements(
                $buyingPaymentMethodsIds,
                static::getFaker()->numberBetween(1, count($buyingPaymentMethodsIds))
            );
        } else {
            $orderBuyingPaymentMethodsIds = [];
            $buyingCashOn = CurrencyExchangeOrder::CASH_ON;
        }

        $model = new CurrencyExchangeOrder([
            'selling_currency_id' => $sellCurrencyId,
            'buying_currency_id' => $buyCurrencyId,
            'user_id' => $user->id,
            'selling_rate' => $crossRateOn ? null :
                static::getFaker()->valid(static function ($v) {
                    return (bool)$v;
                })->randomFloat(1, 0.01, 10),
            'buying_rate' => $crossRateOn ? null :
                static::getFaker()->valid(static function ($v) {
                    return (bool)$v;
                })->randomFloat(1, 0.01, 10),
            'selling_currency_min_amount' => $min_amount = static::getFaker()->randomNumber(2),
            'selling_currency_max_amount' => $min_amount + static::getFaker()->randomNumber(2),
            'status' => CurrencyExchangeOrder::STATUS_ON,
            'selling_delivery_radius' => static::getFaker()->randomNumber(3),
            'buying_delivery_radius' => static::getFaker()->randomNumber(3),
            'selling_location_lat' => $orderSellingLat,
            'selling_location_lon' => $orderSellingLon,
            'buying_location_lat' => $orderBuyingLat,
            'buying_location_lon' => $orderBuyingLon,
            'selling_cash_on' => $sellingCashOn,
            'buying_cash_on' => $buyingCashOn,
            'cross_rate_on' => $crossRateOn,
        ]);

        if (!$model->save()) {
            throw new ARGeneratorException("Can't save " . static::classNameModel() . "!\r\n");
        }

        $this->service->updatePaymentMethods($model, $orderSellingPaymentMethodsIds, $orderBuyingPaymentMethodsIds);

        return $model;
    }

    /**
     * @throws ARGeneratorException
     */
    public function load(): ActiveRecord
    {
        return $this->factoryModel();
    }

    /**
     * @param int $currencyId
     * @return int[] array
     */
    private function getPaymentMethodsIds(int $currencyId): array
    {
        return array_map('intval',
            ArrayHelper::getColumn(
                PaymentMethod::find()->joinWith('currencies c')
                    ->where(['c.id' => $currencyId])
                    ->select('{{%payment_method}}.id')
                    ->limit(8)
                    ->asArray()
                    ->all(),
                'id'
            )
        );
    }

    /**
     * @return int[]
     */
    private function getRandCurrenciesPair(): array
    {
        $currenciesPairIds = Currency::find()
            ->select('id')
            ->where(['in', 'code', ['USD', 'EUR', 'RUB']])
            ->orderByRandAlt(2)
            ->asArray()
            ->all();

        if (!$currenciesPairIds || count($currenciesPairIds) !== 2) {
            $class = self::classNameModel();
            $message = "\n$class: creation skipped. There is no Currencies yet.\n";
            $message .= "It's not error - few iterations later new ExchangeOrder will be generated.\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);

            return [];
        }

        return [$currenciesPairIds[0]['id'], $currenciesPairIds[1]['id']];
    }

    private function findUser(): ?User
    {
        $user = User::find()
            ->orderByRandAlt(1)
            ->one();

        if (!$user) {
            $class = self::classNameModel();
            $message = "\n$class: creation skipped. There is no Users\n";
            $message .= "It's not error - few iterations later new ExchangeOrder will be generated.\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);
        }

        return $user;
    }
}
