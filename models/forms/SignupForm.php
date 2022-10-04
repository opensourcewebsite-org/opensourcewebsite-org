<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\User;
use Yii;
use yii\base\Model;

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
            ['captcha', 'captcha', 'skipOnEmpty' => YII_ENV_TEST || YII_ENV_DEV],
            ['username', 'trim'],
            [['username', 'password', 'password_repeat'], 'required'],
            ['username', 'string', 'max' => 255],
            ['username', 'validateUsername'],
            [
                'username', 'unique', 'targetClass' => User::class,
                'message' => 'This username has already been taken.',
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
            $this->password = null;
            $this->password_repeat = null;
            $this->captcha = null;

            return false;
        }

        $user = $this->factoryUser();

        return $user->save() ? Yii::$app->user->login($user, 30 * 24 * 60 * 60) : null;
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
