<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

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
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

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
            [['user_id', 'selling_currency_id', 'buying_currency_id'], 'required'],
            [['user_id', 'selling_currency_id', 'buying_currency_id', 'status', 'renewed_at', 'delivery_radius', 'created_at', 'processed_at', 'selling_cash_on', 'buying_cash_on'], 'integer'],
            [['selling_rate', 'buying_rate', 'selling_currency_min_amount', 'selling_currency_max_amount'], 'number'],
            [['location_lat', 'location_lon'], 'string', 'max' => 255],

            [['created_at', 'renewed_at'], 'safe'],

            [['status'], 'default', 'value' => self::STATUS_INACTIVE],
            [['delivery_radius'], 'default', 'value' => 0],
            [['location_lat'], 'default', 'value' => ''],
            [['location_lon'], 'default', 'value' => ''],
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
            'selling_currency_id' => Yii::t('app', 'Sell'),
            'buying_currency_id' => Yii::t('app', 'Buy'),
            'selling_rate' => Yii::t('app', 'Rate'),
            'buying_rate' => Yii::t('app', 'Reverse Rate'),
            'selling_currency_min_amount' => Yii::t('app', 'Min Amount'),
            'selling_currency_max_amount' => Yii::t('app', 'Max Amount'),
            'status' => Yii::t('app', 'Status'),
            'renewed_at' => 'Renewed At',
            'delivery_radius' => Yii::t('app', 'Delivery Radius'),
            'location_lat' => Yii::t('app', 'Latitude'),
            'location_lon' => Yii::t('app', 'Longitude'),
            'created_at' => 'Created At',
            'processed_at' => 'Processed At',
            'selling_cash_on' => 'Selling Cash On',
            'buying_cash_on' => 'Buying Cash On',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'renewed_at',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeValidate() {
        $this->buying_rate = ((float) 1) / $this->selling_rate;

        return parent::beforeValidate();
    }

    /**
     * @return Currency|null
     */
    public function getSellingCurrency()
    {
        return Currency::findOne(['id' => $this->selling_currency_id]);
    }

    /**
     * @return Currency|null
     */
    public function getBuyingCurrency()
    {
        return Currency::findOne(['id' => $this->buying_currency_id]);
    }

    /**
     * @param string $location
     * @return $this
     */
    public function setLocation(string $location): self
    {
        $latLon = explode(',', $location);
        if (count($latLon) === 2) {
            $this->location_lat = $latLon[0] ?? '';
            $this->location_lon = $latLon[1] ?? '';
        }

        return $this;
    }
}
