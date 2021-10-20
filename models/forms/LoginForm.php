<?php

declare(strict_types=1);

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $captcha;

    /**
     * @var User
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['captcha', 'captcha', 'skipOnEmpty' => YII_ENV_TEST || YII_ENV_DEV],
            ['username', 'trim'],
            [['username', 'password'], 'required'],
            ['username', 'string', 'max' => 255],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username') . ' / ID / ' . Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'captcha' => Yii::t('app', 'Captcha'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        $user = $this->getUser();

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Incorrect username or password.');
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
            $this->captcha = null;

            return false;
        }

        $user = $this->getUser();

        return Yii::$app->user->login($user, 30 * 24 * 60 * 60);
    }

    protected function getUser()
    {
        if ($this->user === null) {
            $this->user = User::findByUsername($this->username) ?: User::findById($this->username) ?: User::findByEmail($this->username) ?: null;
        }

        return $this->user;
    }
}
