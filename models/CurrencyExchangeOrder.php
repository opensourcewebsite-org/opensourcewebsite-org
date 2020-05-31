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
 *
 * @property Currency $buyingCurrency
 * @property Currency $sellingCurrency
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
            [['user_id', 'selling_currency_id', 'buying_currency_id', 'renewed_at'], 'required'],
            [['user_id', 'selling_currency_id', 'buying_currency_id', 'status', 'renewed_at'], 'integer'],
            [['selling_rate', 'buying_rate', 'selling_currency_min_amount', 'selling_currency_max_amount'], 'number'],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSellingCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'selling_currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuyingCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'buying_currency_id']);
    }

    public static function getAllAvailablePairs()
    {
        return self::find()
            ->select([
                "(selling_currency.code || '/' || buying_currency.code) as pair_code",
                'buying_currency_id',
                'selling_currency_id',
            ])
            ->innerJoin(
                'currency selling_currency',
                'selling_currency_id = selling_currency.id'
            )
            ->innerJoin(
                'currency buying_currency',
                'buying_currency_id = buying_currency.id'
            );
    }
}
