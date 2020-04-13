<?php

namespace app\models\profile;

use app\models\User;
use yii\base\Model;

class Email extends Model
{

    public $email;

    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'validateEmailUnique'],
        ];
    }

    public function validateEmailUnique()
    {
        $email = User::findOne(['email' => $this->email]);
        if ($email) {
            $this->addError('email', 'Email must be unique.');
        }
    }
}
