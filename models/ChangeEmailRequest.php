<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Password reset request form
 */
class ChangeEmailRequest extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%change_email_request}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'token', 'email'], 'required'],
            [
                ['user_id'],
                'exist',
                'targetAttribute' => 'id',
                'targetClass' => User::class,
                'filter' => ['status' => User::STATUS_ACTIVE],
            ],
            [['user_id'], 'integer'],
            [['token'], 'string'],
            [['email'], 'email'],
        ];
    }

    /**
     * Sends an email with a link, for changing the email.
     *
     * @return bool whether the email was send
     */
    public function sendEmail()
    {
        if (!$this->save()) {
            return false;
        }

        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'id' => $this->user_id,
        ]);

        $link = Yii::$app->urlManager->createAbsoluteUrl(['site/change-email', 'token' => $this->token]);

        return Yii::$app
            ->mailer
            ->compose('change-email', [
                'user' => $user,
                'link' => $link,
            ])
            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name . ' Robot'])
            ->setTo($this->email)
            ->setSubject('Email change for ' . Yii::$app->name)
            ->send();
    }
}
