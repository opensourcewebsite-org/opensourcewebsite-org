<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\User as GlobalUser;

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
 * @property int $delivery_radius
 * @property string|null $location_lat
 * @property string|null $location_lon
 * @property int $created_at
 * @property int|null $processed_at
 * @property int $selling_cash_on
 * @property int $buying_cash_on
 * @property int $cross_rate_on
 *
 * @property CurrencyExchangeOrderBuyingPaymentMethod[] $currencyExchangeOrderBuyingPaymentMethods
 * @property CurrencyExchangeOrderMatch[] $currencyExchangeOrderMatches
 * @property CurrencyExchangeOrderMatch[] $currencyExchangeOrderMatches0
 * @property CurrencyExchangeOrderSellingPaymentMethod[] $currencyExchangeOrderSellingPaymentMethods
 */
class CurrencyExchangeOrder extends \yii\db\ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

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
            [
                [
                    'user_id',
                    'selling_currency_id',
                    'buying_currency_id',
                    'created_at',
                ],
                'required',
            ],
            [
                [
                    'user_id',
                    'selling_currency_id',
                    'buying_currency_id',
                    'status',
                    'delivery_radius',
                    'created_at',
                    'processed_at',
                    'selling_cash_on',
                    'buying_cash_on',
                    'cross_rate_on',
                ],
                'integer',
            ],
            [
                [
                    'selling_rate',
                    'buying_rate',
                    'selling_currency_min_amount',
                    'selling_currency_max_amount',
                    'location_lat',
                    'location_lon',
                ],
                'double',
            ],
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
            'delivery_radius' => 'Delivery Radius',
            'location_lat' => 'Location Lat',
            'location_lon' => 'Location Lon',
            'created_at' => 'Created At',
            'processed_at' => 'Processed At',
            'selling_cash_on' => 'Selling Cash On',
            'buying_cash_on' => 'Buying Cash On',
            'cross_rate_on' => 'Cross Rate On',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * Gets query for [[CurrencyExchangeOrderSellingPaymentMethods]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyExchangeOrderSellingPaymentMethods()
    {
        return $this->hasMany(CurrencyExchangeOrderSellingPaymentMethod::className(), ['order_id' => 'id']);

    }

    /**
     * Gets query for [[CurrencyExchangeOrderBuyingPaymentMethods]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyExchangeOrderBuyingPaymentMethods()
    {
        return $this->hasMany(CurrencyExchangeOrderBuyingPaymentMethod::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMatches()
    {
        return $this->hasMany(self::className(), ['id' => 'match_order_id'])
            ->viaTable('{{%currency_exchange_order_match}}', ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getCounterMatches()
    {
        return $this->hasMany(self::className(), ['id' => 'order_id'])
            ->viaTable('{{%currency_exchange_order_match}}', ['match_order_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('matches', true);
        $this->unlinkAll('counterMatches', true);

        return true;
    }

    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id']);
    }

    public function getTitle()
    {
        return $this->getSellingCurrency->code . '/' . $this->getBuyingCurrency->code;
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

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ON;
    }
}
