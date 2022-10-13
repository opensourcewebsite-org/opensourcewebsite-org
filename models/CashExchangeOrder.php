<?php

namespace app\models;

use Yii;

class CashExchangeOrder extends CurrencyExchangeOrder
{
    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'selling_currency_id' => Yii::t('bot', 'Selling currency'),
            'buying_currency_id' => Yii::t('bot', 'Buying currency'),
            'sellingCurrency' => Yii::t('bot', 'Selling currency'),
            'buyingCurrency' => Yii::t('bot', 'Buying currency'),
            'selling_rate' => Yii::t('bot', 'Exchange rate'),
            'buying_rate' => Yii::t('bot', 'Inverse rate'),
            'selling_currency_min_amount' => Yii::t('bot', 'Min. amount'),
            'selling_currency_max_amount' => Yii::t('bot', 'Max. amount'),
            'status' => Yii::t('bot', 'Status'),
            'selling_delivery_radius' => Yii::t('bot', 'Search radius'),
            'buying_delivery_radius' => Yii::t('bot', 'Buying delivery radius'),
            'selling_location' => Yii::t('bot', 'Location'),
            'selling_location_lat' => 'Location Lat',
            'selling_location_lon' => 'Location Lon',
            'buying_location_lat' => 'Location Lat',
            'buying_location_lon' => 'Location Lon',
            'created_at' => 'Created At',
            'processed_at' => 'Processed At',
            'selling_cash_on' => Yii::t('bot', 'Cash'),
            'buying_cash_on' => Yii::t('bot', 'Cash'),
            'selling_currency_label' => Yii::t('app', 'Label'),
            'buying_currency_label' => Yii::t('app', 'Label'),
            'sellingPaymentMethodIds' => Yii::t('app', 'Selling payment methods'),
            'buyingPaymentMethodIds' => Yii::t('app', 'Buying payment methods'),
        ];
    }
}
