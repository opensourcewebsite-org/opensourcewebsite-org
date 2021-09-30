<?php

namespace app\models;

use app\components\helpers\ReferrerHelper;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $password;
    public $password_repeat;
    public $captcha;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['captcha', 'captcha', 'skipOnEmpty' => YII_ENV_TEST],
            [['username', 'password'], 'required'],
            ['username', 'trim'],
            ['username', 'string', 'max' => 255],
            ['username', 'validateUsername'],
            [
                'username', 'unique', 'targetClass' => User::class,
                'message' => 'This username address has already been taken.',
            ],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'string'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'password_repeat' => Yii::t('app', 'Password Repeat'),
            'captcha' => Yii::t('app', 'Captcha'),
        ];
    }

    public function validateUsername($attribute, $params)
    {
        if ($this->hasErrors()) {
            return false;
        }

        $user = new User();
        $user->username = $this->username;

        if (!$user->validate('username')) {
            $this->addErrors($user->getErrors());
        }
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            $this->captcha = null;

            return false;
        }

        $user = $this->factoryUser();

        // If referrer exists then add referrer id in user table
        $referrerID = ReferrerHelper::getReferrerIdFromCookie();

        if ($referrerID != null) {
            $user->referrer_id = $referrerID;
        }

        return $user->save() ? Yii::$app->user->login($user, 3600 * 24 * 30) : null;
    }

    public function factoryUser(): User
    {
        $user = new User();
        $user->username = $this->username;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user;
    }
}
