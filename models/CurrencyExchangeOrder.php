<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "currency_exchange_order".
 *
 * @property int $id
 * @property int $user_id
 * @property int $selling_currency_id
 * @property int $buying_currency_id
 * @property float|null $selling_rate
 * @property float|null $buying_rate
 * @property float|null $selling_currency_min_amount
 * @property float|null $selling_currency_max_amount
 * @property int $status
 * @property int $renewed_at
 * @property int $delivery_radius
 * @property string $location_lat
 * @property string $location_lon
 * @property int $created_at
 * @property int|null $processed_at
 * @property int $selling_cash_on
 * @property int $buying_cash_on
 */
class CurrencyExchangeOrder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency_exchange_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'selling_currency_id', 'buying_currency_id', 'renewed_at', 'delivery_radius', 'location_lat', 'location_lon', 'created_at'], 'required'],
            [['user_id', 'selling_currency_id', 'buying_currency_id', 'status', 'renewed_at', 'delivery_radius', 'created_at', 'processed_at', 'selling_cash_on', 'buying_cash_on'], 'integer'],
            [['selling_rate', 'buying_rate', 'selling_currency_min_amount', 'selling_currency_max_amount'], 'number'],
            [['location_lat', 'location_lon'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'selling_currency_id' => 'Selling Currency ID',
            'buying_currency_id' => 'Buying Currency ID',
            'selling_rate' => 'Selling Rate',
            'buying_rate' => 'Buying Rate',
            'selling_currency_min_amount' => 'Selling Currency Min Amount',
            'selling_currency_max_amount' => 'Selling Currency Max Amount',
            'status' => 'Status',
            'renewed_at' => 'Renewed At',
            'delivery_radius' => 'Delivery Radius',
            'location_lat' => 'Location Lat',
            'location_lon' => 'Location Lon',
            'created_at' => 'Created At',
            'processed_at' => 'Processed At',
            'selling_cash_on' => 'Selling Cash On',
            'buying_cash_on' => 'Buying Cash On',
        ];
    }
}
