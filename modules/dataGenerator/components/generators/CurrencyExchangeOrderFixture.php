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

        $fee = $this->faker->boolean() ?
            $this->faker->valid(static function ($v) {
                return (bool)$v;
            })->randomFloat(2, -20, 20)
            : null;

        $min_amount = $this->faker->boolean() ? $min_amount = $this->faker->randomNumber(2) : null;
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
            'selling_delivery_radius' => $this->faker->boolean() ? $this->faker->randomNumber(3) : null,
            'buying_delivery_radius' => $this->faker->boolean() ? $this->faker->randomNumber(3) : null,
            'selling_location_lat' => $sellingCashOn ? $sellingLocationLat : null,
            'selling_location_lon' => $sellingCashOn ? $sellingLocationLon : null,
            'buying_location_lat' => $buyingCashOn ? $buyingLocationLat : null,
            'buying_location_lon' => $buyingCashOn ? $buyingLocationLon : null,
            'selling_cash_on' => $sellingCashOn,
            'buying_cash_on' => $buyingCashOn,
            'selling_currency_label' => $this->faker->boolean() ? $this->faker->sentence() : null,
            'buying_currency_label' => $this->faker->boolean() ? $this->faker->sentence() : null,
        ]);

        if (!$model->save()) {
            var_dump($model->getErrors());
            throw new ARGeneratorException(static::classNameModel() . ': can\'t save.' . "\r\n");
        }

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

        return $model;
    }

    /**
     * @throws ARGeneratorException
     */
    public function load(): ?ActiveRecord
    {
        return $this->factoryModel();
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
