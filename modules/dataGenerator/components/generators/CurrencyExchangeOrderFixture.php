<?php

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;
use app\models\User;
use app\helpers\LatLonHelper;
use yii\db\ActiveRecord;
use yii\helpers\Console;
use app\models\matchers\ModelLinker;

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

        $fee = $this->faker->optional(0.5, 0)->randomFloat(2, -20, 20);

        $min_amount = $this->faker->optional(0.5, null)->randomNumber(2);
        $max_amount = $this->faker->boolean() ?
            (isset($min_amount) ?
                $min_amount + $this->faker->randomNumber(2)
                : $this->faker->randomNumber(2))
            : null;

        $model = new CurrencyExchangeOrder([
            'user_id' => $user->id,
            'selling_currency_id' => $sellingCurrency->id,
            'buying_currency_id' => $buyingCurrency->id,
            'fee' => $fee,
            'selling_currency_min_amount' => $min_amount,
            'selling_currency_max_amount' => $max_amount,
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
