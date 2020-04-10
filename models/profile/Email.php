<?php

namespace app\models\profile;

use yii\base\Model;

class Email extends Model
{

    public $email;

    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
        ];
    }
}
