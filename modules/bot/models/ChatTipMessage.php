<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\models\Currency;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_tip_message".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $currency_id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property float $amount
 * @property int $message_id
 *
 * @property Chat $chat
 * @property Currency $currency
 * @property User $fromUser
 * @property User $toUser
 *
 * @package app\modules\bot\models
 */
class ChatTipMessage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_tip_message}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'currency_id', 'from_user_id', 'to_user_id', 'amount','message_id'], 'required'],
            [['chat_id', 'currency_id', 'from_user_id', 'to_user_id', 'message_id'], 'integer'],
            ['amount', 'double', 'min' => 0, 'max' => 9999999999999.99],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency_id' => 'id']],
            [['from_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['from_user_id' => 'id']],
            [['to_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['to_user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('bot', 'ID'),
            'chat_id' => Yii::t('bot', 'Chat ID'),
            'currency_id' => Yii::t('bot', 'Currency ID'),
            'from_user_id' => Yii::t('bot', 'From User ID'),
            'to_user_id' => Yii::t('bot', 'To User ID'),
            'amount' => Yii::t('bot', 'Amount'),
            'message_id' => Yii::t('bot', 'Message ID'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    /**
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency(): ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * Gets query for [[FromUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::class, ['id' => 'from_user_id']);
    }

    /**
     * Gets query for [[ToUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::class, ['id' => 'to_user_id']);
    }
}
