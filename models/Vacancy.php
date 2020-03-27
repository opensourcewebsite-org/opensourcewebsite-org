<?php

namespace app\models;

use Yii;
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
            TimestampBehavior::className(),
        ];
    }

    public function rules()
    {
        return [
            [
                [
                    'company_id',
                    'views',
                    'status',
                    'sex',
                    'location_lat',
                    'location_lon',
                    'location_at',
                ],
                'integer',
            ],
            [
                [
                    'name',
                    'employment',
                    'hours_of_employment',
                    'salary',
                ],
                'string',
                'max' => 256,
            ],
            [
                [
                    'sex',
                ],
                'default',
                'value' => 0,
            ],
            [
                [
                    'requirements',
                    'skills_description',
                    'conditions',
                    'responsibility',
                ],
                'string',
            ],
            [
                [
                    'company_id',
                    'name',
                    'employment',
                    'hours_of_employment',
                    'salary',
                    'requirements',
                    'skills_description',
                    'conditions',
                    'responsibility',
                ],
                'required',
            ],
        ];
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
}
