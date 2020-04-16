<?php

namespace app\models;

use app\components\helpers\ReferrerHelper;
use yii\base\Model;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email', 'unique', 'targetClass' => User::class,
                'message' => 'This email address has already been taken.',
            ],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = $this->factoryUser();

        //If referrer exists then add referrer id in user table
        $referrerID = ReferrerHelper::getReferrerIdFromCookie();
        if ($referrerID != null) {
            $user->referrer_id = $referrerID;
        }

        return $user->save() ? $user : null;
    }

    /**
     * Confirm user email.
     *
     * @param int $id the user id
     * @param string $auth_key the user auth_key
     *
     * @return User|null the saved model or null if saving fails
     */
    public static function confirmEmail(int $id, string $authKey)
    {
        $user = User::findOne(['id' => $id, 'is_authenticated' => false]);

        if ($user && $user->validateAuthKey($authKey)) {
            $user->setActive();
            if ($user->save()) {
                return $user;
            }
        }
        return null;
    }

    public function factoryUser(): User
    {
        $user = new User();
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user;
    }
}
