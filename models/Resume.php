<?php


namespace app\models;

use app\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class Resume
 *
 * @package app\models
 */
class Resume extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 14;

    /** @inheritDoc */
    public static function tableName()
    {
        return '{{%resume}}';
    }

    /** @inheritDoc */
    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'currency_id',
                    'status',
                    'created_at',
                    'renewed_at',
                    'processed_at',
                ],
                'integer',
            ],
            [
                [
                    'min_hourly_rate',
                    'location_lat',
                    'location_lon',
                ],
                'double'
            ],
            [
                [
                    'name',
                ],
                'string',
                'max' => 256,
            ],
            [
                [
                    'experiences',
                    'expectations',
                    'skills',
                ],
                'string',
            ],
            [
                [
                    'user_id',
                    'currency_id',
                    'name',
                ],
                'required',
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, [ 'id' => 'currency_id' ]);
    }

    /** @inheritDoc */
    public function behaviors()
    {
        return [
            'TimestampBehavior' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ON && (time() - $this->renewed_at) <= self::LIVE_DAYS * 24 * 60 * 60;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyRelation()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        $currency = $this->currencyRelation;
        if ($currency) {
            $currencyCode = $currency->code;
        } else {
            $currencyCode = '';
        }

        return $currencyCode;
    }
}
