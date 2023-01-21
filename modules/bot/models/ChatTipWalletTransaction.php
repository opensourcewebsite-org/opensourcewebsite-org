<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\models\WalletTransaction;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_tip_wallet_transaction".
 *
 * @property int $id
 * @property int $chat_tip_id
 * @property int $transaction_id
 *
 * @property ChatTip $chatTip
 * @property WalletTransaction $walletTransaction
 */
class ChatTipWalletTransaction extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_tip_wallet_transaction}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_tip_id', 'transaction_id'], 'required'],
            [['chat_tip_id', 'transaction_id'], 'integer'],
            [['chat_tip_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatTip::class, 'targetAttribute' => ['chat_tip_id' => 'id']],
            [['transaction_id'], 'exist', 'skipOnError' => true, 'targetClass' => WalletTransaction::class, 'targetAttribute' => ['transaction_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chat_tip_id' => 'Chat Tip ID',
            'transaction_id' => 'Wallet Transaction ID',
        ];
    }

    /**
     * Gets query for [[ChatTip]].
     *
     * @return ActiveQuery
     */
    public function getChatTip()
    {
        return $this->hasOne(ChatTip::class, ['id' => 'chat_tip_id']);
    }

    /**
     * Gets query for [[ChatPhrase]].
     *
     * @return ActiveQuery
     */
    public function getWalletTransaction()
    {
        return $this->hasOne(WalletTransaction::class, ['id' => 'transaction_id']);
    }
}
