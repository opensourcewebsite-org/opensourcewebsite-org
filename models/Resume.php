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
}