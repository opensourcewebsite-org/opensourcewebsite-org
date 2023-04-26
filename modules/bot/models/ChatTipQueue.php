<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\models\Currency;
use app\models\WalletTransaction;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_tip_queue".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $currency_id
 * @property int $message_id
 * @property int $user_count
 * @property float $user_amount
 *
 * @package app\modules\bot\models
 */
class ChatTipQueue extends ActiveRecord
{
    public const USER_MIN_COUNT = 1;
    public const USER_MAX_COUNT = 100;

    public const USER_MIN_AMOUNT = 0.01;
    public const USER_MAX_AMOUNT = 9999999999999.99;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_tip_queue}}';
    }

    public function init()
    {
        parent::init();
        if ($this->isNewRecord) {
            $this->user_count = $this->user_count ?? self::USER_MIN_COUNT;
            $this->user_amount = $this->user_amount ?? self::USER_MIN_AMOUNT;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'currency_id', 'message_id'], 'integer', 'required'],
            [['user_count'], 'integer', 'min' => self::USER_MIN_COUNT, 'max' => self::USER_MAX_COUNT],
            [['user_amount'], 'integer', 'min' => self::USER_MIN_AMOUNT, 'max' => self::USER_MAX_AMOUNT],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'chat_id' => Yii::t('bot', 'Chat ID'),
            'currency_id' => Yii::t('app', 'Currency'),
            'message_id' => Yii::t('bot', 'Message ID'),
            'user_count' => Yii::t('app', 'User count'),
            'user_amount' => Yii::t('app', 'User amount'),
        ];
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

    public function getChatId()
    {
        return $this->chat_id;
    }

    /**
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getCurrencyId()
    {
        return $this->currency_id;
    }

    public function getMessageId()
    {
        return $this->currency_id;
    }

    public function getUserCount()
    {
        return $this->user_count ?? self::USER_MIN_COUNT;
    }

    public function getUserAmount()
    {
        return $this->user_amount ?? self::USER_MIN_AMOUNT;
    }
}
