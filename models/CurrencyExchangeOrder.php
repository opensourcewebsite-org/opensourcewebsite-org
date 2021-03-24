<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\User as GlobalUser;
use app\modules\bot\validators\RadiusValidator;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

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
 * @property int $selling_delivery_radius
 * @property string|null $selling_location_lat
 * @property string|null $selling_location_lon
 * @property int $buying_delivery_radius
 * @property string|null $buying_location_lat
 * @property string|null $buying_location_lon
 * @property int $created_at
 * @property int|null $processed_at
 * @property int $selling_cash_on
 * @property int $buying_cash_on
 * @property int $cross_rate_on
 *
 * @property User $user
 * @property CurrencyExchangeOrderBuyingPaymentMethod[] $currencyExchangeOrderBuyingPaymentMethods
 * @property CurrencyExchangeOrderMatch[] $currencyExchangeOrderMatches
 * @property CurrencyExchangeOrderMatch[] $currencyExchangeOrderMatches0
 * @property CurrencyExchangeOrderSellingPaymentMethod[] $currencyExchangeOrderSellingPaymentMethods
 */
class CurrencyExchangeOrder extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    public const CROSS_RATE_OFF = 0;
    public const CROSS_RATE_ON = 1;

    public const CASH_OFF = 0;
    public const CASH_ON = 1;

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
    public function rules(): array
    {
        return [
            [
                [
                    'user_id',
                    'selling_currency_id',
                    'buying_currency_id',
                ],
                'required',
            ],
            [
                [
                    'user_id',
                    'selling_currency_id',
                    'buying_currency_id',
                    'status',
                    'selling_delivery_radius',
                    'buying_delivery_radius',
                    'created_at',
                    'processed_at',
                    'selling_cash_on',
                    'buying_cash_on',
                    'cross_rate_on',
                ],
                'integer',
            ],
            [
                ['selling_delivery_radius','buying_delivery_radius'],
                RadiusValidator::class,
            ],
            [
                ['selling_location_lat','buying_location_lat'],
                LocationLatValidator::class,
            ],
            [
                ['selling_location_lon', 'buying_location_lat'],
                LocationLonValidator::class,
            ],
            ['selling_location', 'required', 'when' => function ($model) {
                    if ($model->selling_cash_on && !$model->selling_location) {
                        return true;
                    }
                    return false;
                }, 'whenClient' => new JsExpression("function(attribute, value) {
                    return $('#cashSellCheckbox').prop('checked');
                }")
            ],

            ['buying_location', 'required', 'when' => function ($model) {
                    if ($model->buying_cash_on && !$model->buying_location) {
                        return true;
                    }
                    return false;
                }, 'whenClient' => new JsExpression("function(attribute, value) {
                    return $('#cashBuyCheckbox').prop('checked');
                }")
            ],

            [['selling_location', 'buying_location'], function($attribute) {
                [$lat, $lon] = explode(',', $this->$attribute);
                if (!(new LocationLatValidator())->validateLat($lat) ||
                    !(new LocationLonValidator())->validateLon($lon)
                ) {
                    $this->addError($attribute, Yii::t('app', 'Incorrect Location!'));
                }
            }],

            [
                ['selling_location', 'buying_location'], 'string',
            ],
            [
                [
                    'selling_rate',
                    'buying_rate',
                ],
                'double',
                'min' => 0,
                'max' => 9999999999999.99999999,
            ],
            [
                [
                    'selling_currency_min_amount',
                    'selling_currency_max_amount',
                ],
                'filter', 'filter' => function ($value) {
                return ($value != 0 ? $value : null);
            },
            ],
            [
                [
                    'selling_currency_min_amount',
                    'selling_currency_max_amount',
                ],
                'double',
                'min' => 0,
                'max' => 9999999999.99999999,
            ],
            [
                [
                    'selling_currency_max_amount',
                ],
                'compare',
                'when' => function ($model) {
                    return $model->selling_currency_min_amount != null;
                },
                'whenClient' => new JsExpression("
                    function (attribute, value) {
                        return $('#currencyexchangeorder-selling_currency_min_amount').val() != ''
                    }"
                ),
                'compareAttribute' => 'selling_currency_min_amount', 'operator' => '>=', 'type' => 'number'
            ]
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
            'selling_currency_id' => 'Selling Currency',
            'buying_currency_id' => 'Buying Currency',
            'selling_rate' => Yii::t('bot', 'Exchange rate'),
            'buying_rate' => Yii::t('bot', 'Reverse exchange rate'),
            'selling_currency_min_amount' => Yii::t('bot', 'Min. amount'),
            'selling_currency_max_amount' => Yii::t('bot', 'Max. amount'),
            'status' => Yii::t('bot', 'Status'),
            'selling_delivery_radius' => Yii::t('bot', 'Selling delivery radius'),
            'buying_delivery_radius' => Yii::t('bot', 'Buying delivery radius'),
            'selling_location_lat' => 'Location Lat',
            'selling_location_lon' => 'Location Lon',
            'buying_location_lat' => 'Location Lat',
            'buying_location_lon' => 'Location Lon',
            'created_at' => 'Created At',
            'processed_at' => 'Processed At',
            'selling_cash_on' => Yii::t('bot', 'Cash'),
            'buying_cash_on' => Yii::t('bot', 'Cash'),
            'cross_rate_on' => Yii::t('bot', 'Cross Rate'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }


    public function setSelling_location(string $location): self
    {
        $latLon = explode(',', $location);
        if (count($latLon) === 2) {
            $this->selling_location_lat = $latLon[0] ?? '';
            $this->selling_location_lon = $latLon[1] ?? '';
        }
        return $this;
    }

    public function getSelling_location(): string
    {
        return ($this->selling_location_lat && $this->selling_location_lon) ?
            implode(',', [$this->selling_location_lat, $this->selling_location_lon]) :
            '';
    }

    public function setBuying_location(string $location): self
    {
        $latLon = explode(',', $location);
        if (count($latLon) === 2) {
            $this->buying_location_lat = $latLon[0] ?? '';
            $this->buying_location_lon = $latLon[1] ?? '';
        }
        return $this;
    }

    public function getBuying_location(): string
    {
        return ($this->buying_location_lat && $this->buying_location_lon) ?
            implode(',', [$this->buying_location_lat, $this->buying_location_lon]) :
            '';
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[CurrencyExchangeOrderSellingPaymentMethods]].
     *
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getSellingPaymentMethods(): ActiveQuery
    {
        return $this->hasMany(PaymentMethod::class, ['id' => 'payment_method_id'])
            ->viaTable('{{%currency_exchange_order_selling_payment_method}}', ['order_id' => 'id']);
    }

    /**
     * Gets query for [[CurrencyExchangeOrderBuyingPaymentMethods]].
     *
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getBuyingPaymentMethods(): ActiveQuery
    {
        return $this->hasMany(PaymentMethod::class, ['id' => 'payment_method_id'])
            ->viaTable('{{%currency_exchange_order_buying_payment_method}}', ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMatches(): ActiveQuery
    {
        return $this->hasMany(self::class, ['id' => 'match_order_id'])
            ->viaTable('{{%currency_exchange_order_match}}', ['order_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMatchesOrderedByUserRating(): ActiveQuery
    {
        return $this
            ->getMatches()
            ->joinWith('user u')
            ->orderBy(['u.rating' => SORT_DESC])
            ->addOrderBy(['u.created_at' => SORT_ASC]);;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getCounterMatches(): ActiveQuery
    {
        return $this->hasMany(self::class, ['id' => 'order_id'])
            ->viaTable('{{%currency_exchange_order_match}}', ['match_order_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('matches', true);
        $this->unlinkAll('counterMatches', true);

        $tblName = static::tableName();

        $matchesQuery = static::find()
            ->where(['!=', "$tblName.user_id", $this->user_id])
            ->andWhere(["$tblName.status" => static::STATUS_ON])
            ->andWhere(["$tblName.buying_currency_id" => $this->selling_currency_id])
            ->andWhere(["$tblName.selling_currency_id" => $this->buying_currency_id]);

        if (!$this->cross_rate_on) {
            if ($this->buying_rate) {
                $matchesQuery->andWhere(["$tblName.cross_rate_on" => false])
                    ->andWhere(['<=', "$tblName.selling_rate", $this->buying_rate]);
            }
        } else {
            $matchesQuery->andWhere(["$tblName.cross_rate_on" => true]);
        }

        $buyingMethodsIds = ArrayHelper::getColumn($this->getBuyingPaymentMethods()->asArray()->all(), 'id');
        $sellingMethodsIds = ArrayHelper::getColumn($this->getSellingPaymentMethods()->asArray()->all(), 'id');

        $matchesQuery
            ->joinWith('sellingPaymentMethods sm')
            ->joinWith('buyingPaymentMethods bm');

        if ($this->selling_cash_on && $this->selling_location_lat && $this->selling_location_lon ) {
            $matchesQuery->andWhere(
                ['or',
                    ['and',
                        ['buying_cash_on' => true],
                        "ST_Distance_Sphere(POINT($this->selling_location_lon, $this->selling_location_lat),"
                        ."POINT($tblName.buying_location_lon, $tblName.buying_location_lat)) <= 1000 * ($tblName.buying_delivery_radius + " . ($this->selling_delivery_radius ?: 0) . ')'
                    ],
                    ['in', 'sm.id', $buyingMethodsIds]
                ]);
        } else {
            $matchesQuery->andWhere(['in', 'sm.id', $buyingMethodsIds]);
        }

        if ($this->buying_cash_on && $this->buying_location_lat && $this->buying_location_lon) {
            $matchesQuery->andWhere(
                ['or',
                    ['and',
                        ['selling_cash_on' => true],
                        "ST_Distance_Sphere(POINT($this->buying_location_lon, $this->buying_location_lat),"
                        ."POINT($tblName.selling_location_lon, $tblName.selling_location_lat)) <= 1000 * ($tblName.selling_delivery_radius + " . ($this->buying_delivery_radius ?: 0) . ')'
                    ],
                    ['in', 'bm.id', $sellingMethodsIds]
                ]
            );
        } else {
            $matchesQuery->andWhere(['in', 'bm.id', $sellingMethodsIds]);
        }

        foreach ($matchesQuery->all() as $matchedOrder) {
            $this->link('matches', $matchedOrder);
            $this->link('counterMatches', $matchedOrder);
        }

    }

    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::class, ['id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->sellingCurrency->code . '/' . $this->buyingCurrency->code;
    }

    /**
     * @return string
     */
    public function getReverseTitle()
    {
        return $this->buyingCurrency->code . '/' . $this->sellingCurrency->code;
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

    /**
     * {@inheritdoc}
     */
    public function clearMatches()
    {
        if ($this->processed_at !== null) {
            $this->unlinkAll('matches', true);
            $this->unlinkAll('counterMatches', true);

            $this->setAttributes([
                'processed_at' => null,
            ]);

            $this->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {

        $clearMatches = false;

        if (isset($changedAttributes['status'])) {
            if ($this->status == self::STATUS_OFF) {
                $clearMatches = true;
            }
        }

        if ((isset($changedAttributes['selling_cash_on']) && ((bool)$this->selling_cash_on !== (bool)$changedAttributes['selling_cash_on'])) ||
            (isset($changedAttributes['buying_cash_on']) && ((bool)$this->buying_cash_on !== (bool)$changedAttributes['buying_cash_on']))) {
            $clearMatches = true;
        }

        if (isset($changedAttributes['cross_rate_on']) &&
            ((bool)$this->cross_rate_on !== (bool)$changedAttributes['cross_rate_on'])
        ) {
            if ($this->cross_rate_on == self::CROSS_RATE_ON) {
                $clearMatches = true;
                Yii::warning('cross_rate_on');
            }
            Yii::warning('cross_rate_on2');
        }

        $crossUpdated = false;
        if (
            !$this->cross_rate_on &&
            ( isset($changedAttributes['selling_rate']) &&
                $this->selling_rate != $changedAttributes['selling_rate'])
        ) {
            if (floatval($this->selling_rate) !== 0 ) {
                $this->buying_rate = 1 / $this->selling_rate;
                $crossUpdated = true;
                $this->save();
            }
            $clearMatches = true;
            Yii::warning('selling_rate');
        }

        if (
            !$this->cross_rate_on && !$crossUpdated &&
            (isset($changedAttributes['buying_rate']) &&
                $this->buying_rate != $changedAttributes['buying_rate'])
        ) {
            if (floatval($this->selling_rate) !== 0 ) {
                $this->selling_rate = 1 / $this->buying_rate;
                $this->save();
            }

            $clearMatches = true;
            Yii::warning('buying_rate');
        }

        if (isset($changedAttributes['selling_currency_min_amount'])
            || isset($changedAttributes['selling_currency_max_amount'])) {
            $clearMatches = true;
            Yii::warning('selling_currency_min_amount selling_currency_max_amount');
        }

        if ($clearMatches) {
            $this->clearMatches();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function notPossibleToChangeStatus(): array
    {
        $notFilledFields = [];

        if (!$this->selling_cash_on && !$this->sellingPaymentMethods) {
            $notFilledFields[] = Yii::t('app', 'Need to specify at least one Payment Method for Sell');
        }

        if (!$this->buying_cash_on && !$this->buyingPaymentMethods) {
            $notFilledFields[] = Yii::t('app', 'Need to specify at least one Payment Method for Buy');
        }

        return $notFilledFields;
    }

    public function getSellingCurrencyMinAmount(): string
    {
        if ($this->selling_currency_min_amount) {
            return number_format($this->selling_currency_min_amount, 2);
        } else {
            return '∞';
        }
    }

    public function getSellingCurrencyMaxAmount(): string
    {
        if ($this->selling_currency_max_amount) {
            return number_format($this->selling_currency_max_amount, 2);
        } else {
            return '∞';
        }
    }

    public function getSellingCrossRate()
    {
        return CurrencyRate::find()->where(['from_currency_id' => $this->selling_currency_id, 'to_currency_id' => $this->buying_currency_id])->one();
    }

    public function getBuyingCrossRate()
    {
        return CurrencyRate::find()->where(['from_currency_id' => $this->buying_currency_id, 'to_currency_id' => $this->selling_currency_id])->one();
    }

    public function hasAmount(): bool
    {
        if ($this->selling_currency_min_amount || $this->selling_currency_max_amount) {
            return true;
        }

        return false;
    }

    public function getCurrentSellingPaymentMethodsIds(): array
    {
        return array_map(
            'intval',
            ArrayHelper::getColumn($this->getSellingPaymentMethods()->asArray()->all(), 'id')
        );
    }

    public function getCurrentBuyingPaymentMethodsIds(): array
    {
        return array_map(
            'intval',
            ArrayHelper::getColumn($this->getBuyingPaymentMethods()->asArray()->all(), 'id')
        );
    }
}
