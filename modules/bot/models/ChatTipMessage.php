<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\models\WalletTransaction;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_tip_message".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $transaction_id
 * @property int $message_id
 *
 * @property Chat $chat
 * @property WalletTransaction $transaction
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
            [['chat_id', 'transaction_id', 'message_id'], 'required'],
            [['chat_id', 'transaction_id', 'message_id'], 'integer'],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
            [['transaction_id'], 'exist', 'skipOnError' => true, 'targetClass' => WalletTransaction::class, 'targetAttribute' => ['transaction_id' => 'id']],
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
            'transaction_id' => Yii::t('bot', 'TransactionID'),
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
    public function getWalletTransaction(): ActiveQuery
    {
        return $this->hasOne(WalletTransaction::class, ['id' => 'transaction_id']);
    }
}
