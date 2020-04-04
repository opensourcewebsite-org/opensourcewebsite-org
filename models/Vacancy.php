<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Vacancy extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%vacancy}}';
    }

    public function rules()
    {
        return [
            [
                [
                    'company_id',
                    'currency_id',
                    'status',
                    'gender_id',
                    'location_at',
                    'renewed_at',
                ],
                'integer',
            ],
            [
                [
                    'location_lat',
                    'location_lon',
                    'min_hourly_rate',
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

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, [ 'id' => 'currency_id' ]);
    }
}
