<?php

namespace app\models;

use yii\db\ActiveRecord;

class UserCitizenship extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_citizenship}}';
    }

    public function rules()
    {
        return [
            [ ['user_id', 'country_id' ], 'integer' ],
            [ ['user_id', 'country_id' ], 'required' ],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, [ 'id' => 'user_id' ]);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::class, [ 'id' => 'country_id' ]);
    }
}