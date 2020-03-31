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

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => 'renewed_at',
            ]
        ];
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
                ],
                'integer',
            ],
            [
                [
                    'location_lat',
                    'location_lon',
                    'min_hour_rate',
                    'max_hour_rate',
                ],
                'double'
            ],
            [
                [
                    'name',
                    'employment',
                    'hours_of_employment',
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
                    'employment',
                    'hours_of_employment',
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
