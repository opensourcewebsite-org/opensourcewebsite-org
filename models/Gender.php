<?php

namespace app\models;

use yii\db\ActiveRecord;

class Gender extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%gender}}';
    }

    public function rules()
    {
        return [
            [ [ 'type' ], 'integer' ],
            [ [ 'type' ], 'required' ],
        ];
    }
}