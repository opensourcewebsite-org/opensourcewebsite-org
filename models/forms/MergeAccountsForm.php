<?php

declare(strict_types=1);

namespace app\models\forms;

use Yii;
use app\models\User;

/**
 * Login form
 */
class MergeAccountsForm extends LoginForm
{
    public function rules()
    {
        return [
            ['captcha', 'captcha', 'skipOnEmpty' => YII_ENV_TEST || YII_ENV_DEV],
            ['username', 'trim'],
            [['username', 'password'], 'required'],
            ['username', 'string', 'max' => 255],
            ['password', 'validatePassword'],
            ['username', 'validateUsername'],
        ];
    }

    public function validateUsername($attribute, $params)
    {
        $user = $this->getUser();

        if ($user && ($user->id == Yii::$app->user->id)) {
            $this->addError($attribute, 'Account credentials must be different from the current account.');
        }
    }

    /**
     * Logs in a user using the provided email and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if (!$this->validate()) {
            $this->password = null;
            $this->captcha = null;

            return false;
        }

        return true;
    }
}
