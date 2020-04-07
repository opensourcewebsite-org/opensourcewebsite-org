<?php

namespace app\models\profile;

use yii\base\Model;

class Birthday extends Model
{

    public $birthday;

    public function rules()
    {
        return [
            ['birthday', 'required'],
            ['birthday', 'date'],
        ];
    }
}
