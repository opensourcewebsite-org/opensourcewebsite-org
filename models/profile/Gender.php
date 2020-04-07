<?php

namespace app\models\profile;

use yii\base\Model;

class Gender extends Model
{

    public $gender;

    public function rules()
    {
        return [
            ['gender', 'required'],
        ];
    }
}
