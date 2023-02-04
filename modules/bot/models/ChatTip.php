<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\models\WalletTransaction;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_tip".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $to_user_id
 * @property int|null $message_id
 * @property int $reply_message_id
 * @property int|null $sent_at
 *
 * @property Chat $chat
 * @property User $toUser
 *
 * @package app\modules\bot\models
 */
class ChatTip extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_tip}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'to_user_id', 'reply_message_id'], 'required'],
            [['chat_id', 'to_user_id', 'message_id', 'reply_message_id', 'sent_at'], 'integer'],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
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
            'message_id' => Yii::t('bot', 'Message ID'),
            'reply_message_id' => Yii::t('bot', 'Reply Message ID'),
            'sent_at' => Yii::t('bot', 'Sent At'),
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::class, ['id' => 'to_user_id']);
    }

    public function getWalletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, ['id' => 'transaction_id'])
            ->viaTable(ChatTipWalletTransaction::tableName(), ['chat_tip_id' => 'id']);
    }
}
