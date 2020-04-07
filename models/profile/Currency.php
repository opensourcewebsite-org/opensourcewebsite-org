<?php

namespace app\models\profile;

use yii\base\Model;

class Currency extends Model
{

    public $currency;

    public function rules()
    {
        return [
            ['currency', 'required'],
        ];
    }
}
