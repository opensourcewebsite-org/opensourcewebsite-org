<?php

namespace app\models\forms;

use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;
use app\models\User;

/**
 * Reset password form
 */
class ResetPasswordForm extends Model
{
    public $password;
    public $password_repeat;

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
            ['password', 'trim'],
            [['password', 'password_repeat'], 'required'],
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
            'password' => Yii::t('app', 'New Password'),
            'password_repeat' => Yii::t('app', 'New Password Repeat'),
        ];
    }

    /**
     * Reset password.
     *
     * @param int $id user id
     * @param int $time
     * @param string $hash
     *
     * @return bool if password was reset.
     */
    public function resetPassword(int $id, int $time, string $hash)
    {
        if (!$this->validate()) {
            return false;
        }

        $user = User::findById($id);

        if (!$user || !$user->isEmailConfirmed()) {
            return false;
        }

        if ($userEmail = $user->email) {
            if ($hash == md5($userEmail->email . $user->password_hash . $time)) {
                $user->setPassword($this->password);

                $user->save();

                return Yii::$app->user->login($user, 30 * 24 * 60 * 60);
            }
        }

        return false;
    }
}
