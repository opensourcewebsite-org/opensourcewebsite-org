<?php

namespace app\models\profile;

use yii\base\Model;

class Sexuality extends Model
{

    public $sexuality;

    public function rules()
    {
        return [
            ['sexuality', 'required'],
        ];
    }
}
