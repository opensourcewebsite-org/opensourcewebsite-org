<?php

namespace app\modules\dataGenerator\components\generators;

use app\components\helpers\ArrayHelper;
use app\helpers\LatLonHelper;
use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\matchers\ModelLinker;
use app\models\PaymentMethod;
use app\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class CurrencyExchangeOrderFixture extends ARGenerator
{
    protected function factoryModel(): ?ActiveRecord
    {
        if (!$user = $this->getRandomUser()) {
            return null;
        }

        if (!$currencies = $this->getRandomCurrencies(2)) {
            return null;
        }

        $sellingCurrency = $currencies[0];
        $buyingCurrency = $currencies[1];

        $sellingCashOn = $this->faker->boolean();
        $buyingCashOn = $this->faker->boolean();

        $londonCenter = [51.509865, -0.118092];
        [$sellingLocationLat, $sellingLocationLon] = LatLonHelper::generateRandomPoint($londonCenter, 100);
        [$buyingLocationLat, $buyingLocationLon] = LatLonHelper::generateRandomPoint($londonCenter, 200);

        $sellingRate = $this->faker->optional(0.75)->randomFloat(2, 0.01, 10);
        $buyingRate = $sellingRate ? 1 / $sellingRate : null;

        $minAmount = $this->faker->optional(0.5)->randomNumber(2);
        $maxAmount = $this->faker->boolean() ?
            ($minAmount ? $minAmount + $this->faker->randomNumber(2) : $this->faker->randomNumber(2))
            : null;

        $model = new CurrencyExchangeOrder([
            'user_id' => $user->id,
            'selling_currency_id' => $sellingCurrency->id,
            'buying_currency_id' => $buyingCurrency->id,
            'selling_rate' => $sellingRate,
            'buying_rate' => $buyingRate,
            'selling_currency_min_amount' => $minAmount,
            'selling_currency_max_amount' => $maxAmount,
            'status' => CurrencyExchangeOrder::STATUS_ON,
            'selling_delivery_radius' => $this->faker->optional(0.5, null)->randomNumber(3),
            'buying_delivery_radius' => $this->faker->optional(0.5, null)->randomNumber(3),
            'selling_location_lat' => $sellingCashOn ? $sellingLocationLat : null,
            'selling_location_lon' => $sellingCashOn ? $sellingLocationLon : null,
            'buying_location_lat' => $buyingCashOn ? $buyingLocationLat : null,
            'buying_location_lon' => $buyingCashOn ? $buyingLocationLon : null,
            'selling_cash_on' => $sellingCashOn,
            'buying_cash_on' => $buyingCashOn,
            'selling_currency_label' => $this->faker->optional(0.5, null)->sentence(),
            'buying_currency_label' => $this->faker->optional(0.5, null)->sentence(),
        ]);

        if ($this->save($model)) {
            if (!$sellingCashOn || $this->faker->boolean()) {
                if ($sellingPaymentMethods = $this->getPaymentMethodsByCurrencyId($sellingCurrency->id)) {
                    (new ModelLinker($model))->linkAll('sellingPaymentMethods', $sellingPaymentMethods);
                }
            }

            if (!$buyingCashOn || $this->faker->boolean()) {
                if ($buyingPaymentMethods = $this->getPaymentMethodsByCurrencyId($buyingCurrency->id)) {
                    (new ModelLinker($model))->linkAll('buyingPaymentMethods', $buyingPaymentMethods);
                }
            }
        }

        return $model;
    }

    /**
     * @param int $currencyId
     * @return int[] array
     */
    private function getPaymentMethodsByCurrencyId(int $currencyId): array
    {
        return PaymentMethod::find()
            ->joinWith('currencies c')
            ->where(['c.id' => $currencyId])
            ->limit($this->faker->numberBetween(1, 4))
            ->all();
    }
}
