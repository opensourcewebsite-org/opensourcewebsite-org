<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Vacancy
 * @package app\models
 * @property-read Company $company
 * @property-read Currency $currency
 * @property-read Gender $gender
 * @property int $status
 * @property double $hourly_rate
 * @property string $name
 * @property string $requirements
 * @property string $conditions
 * @property string $responsibilities
 * @property int $id
 */
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
                    'hourly_rate',
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
                    'hourly_rate',
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
