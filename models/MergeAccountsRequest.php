<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Password reset request form
 */
class MergeAccountsRequest extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%merge_accounts_request}}';
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
            [['user_id', 'user_to_merge_id', 'token'], 'required'],
            [
                ['user_id', 'user_to_merge_id'],
                'exist',
                'targetAttribute' => 'id',
                'targetClass' => User::class,
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'There are no users to merge.',
            ],
            [['user_id', 'user_to_merge_id'], 'integer'],
            [['token'], 'string'],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail()
    {
        if (!$this->save())
        {
            return false;
        }

        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'id' => $this->user_id,
        ]);

        /* @var $user User */
        $userToMerge = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'id' => $this->user_to_merge_id,
        ]);

        if (!$user || !$userToMerge) {
            return false;
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'mergeAccountsRequest-html', 'text' => 'mergeAccountsRequest-text'],
                ['user' => $user, 'userToMerge' => $userToMerge, 'mergeAccountsRequestToken' => $this->token]
            )
            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name . ' robot'])
            ->setTo($user->email)
            ->setSubject('Merge accounts request on ' . Yii::$app->name)
            ->send();
    }
}
