<?php

namespace app\models;

use app\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class Vacancy
 *
 * @package app\models
 */
class Vacancy extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 14;

    public static function tableName()
    {
        return '{{%vacancy}}';
    }

    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'company_id',
                    'currency_id',
                    'status',
                    'gender_id',
                    'created_at',
                    'renewed_at',
                    'processed_at',
                ],
                'integer',
            ],
            [
                [
                    'location_lat',
                    'location_lon',
                    'max_hourly_rate',
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
                    'requirements',
                    'conditions',
                    'responsibilities',
                ],
                'string',
            ],
            [
                [
                    'company_id',
                    'currency_id',
                    'name',
                    'requirements',
                    'conditions',
                    'responsibilities',
                ],
                'required',
            ],
        ];
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

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, [ 'id' => 'currency_id' ]);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ON && (time() - $this->renewed_at) <= self::LIVE_DAYS * 24 * 60 * 60;
    }
}
