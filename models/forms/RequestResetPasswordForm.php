<?php

namespace app\models\forms;

use app\models\User;
use app\models\UserEmail;
use Yii;
use yii\base\Model;

/**
 * Request reset password form
 */
class RequestResetPasswordForm extends Model
{
    public $email;
    public $captcha;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['captcha', 'captcha', 'skipOnEmpty' => YII_ENV_TEST || YII_ENV_DEV],
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            [
                'email', 'exist',
                'targetClass' => UserEmail::class,
                'filter' => ['not', ['confirmed_at' => null]],
                'message' => 'There is no user with this email address.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('app', 'Email'),
            'captcha' => Yii::t('app', 'Captcha'),
        ];
    }

    public function afterValidate()
    {
        if ($this->hasErrors()) {
            $this->captcha = null;
        }

        parent::afterValidate();
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findByEmail($this->email);

        if (!$user) {
            return false;
        }

        $time = time();
        $userEmail = $user->email;
        $link = Yii::$app->urlManager->createAbsoluteUrl([
            'site/reset-password',
            'id' => $user->id,
            'time' => $time,
            'hash' => md5($userEmail->email . $user->password_hash . $time),
        ]);

        return Yii::$app->mailer
            ->compose(
                [
                    'html' => 'reset-password-html',
                    'text' => 'reset-password-text',
                ],
                [
                    'user' => $user,
                    'link' => $link,
                ]
            )
            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name . ' Robot'])
            ->setTo($this->email)
            ->setSubject('Reset password for ' . Yii::$app->name)
            ->send();
    }
}
