<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\models\Currency;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_tip_queue_user".
 *
 * @property int $id
 * @property int $queue_id
 * @property int $user_id
 * @property int $transaction_id
 *
 * @package app\modules\bot\models
 */
class ChatTipQueueUser extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_tip_queue_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_id', 'user_id'], 'required'],
            [['queue_id', 'user_id'], 'integer'],
            [['queue_id', 'user_id'], 'unique', 'targetAttribute' => ['queue_id', 'user_id']],
            [['transaction_id'], 'integer'],
            [['queue_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatTipQueue::class, 'targetAttribute' => ['queue_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'queue_id' => Yii::t('bot', 'Queue ID'),
            'user_id' => Yii::t('bot', 'User ID'),
            'transaction_id' => Yii::t('app', 'Transaction'),
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets query for [[ChatTipQueue]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQueue()
    {
        return $this->hasOne(ChatTipQueue::class, ['id' => 'queue_id']);
    }

    public function getQueueId()
    {
        return $this->queue_id;
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Gets query for [[WalletTransaction]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransaction()
    {
        return $this->hasOne(WalletTransaction::class, ['id' => 'transaction_id']);
    }

    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    public static function getActiveUsers()
    {
        return ChatTipQueueUser::findBySql(
            'SELECT i.*
            FROM bot_chat_tip_queue_user i
            JOIN bot_chat_tip_queue q ON q.id = i.queue_id AND q.state = :state
            JOIN LATERAL (
			    SELECT COUNT(*) AS cnt FROM bot_chat_tip_queue_user u WHERE u.queue_id = q.id AND u.transaction_id > 0
            ) u ON u.cnt < q.user_count
            WHERE i.transaction_id IS NULL
            ORDER BY i.id',
            [':state' => ChatTipQueue::OPEN_STATE]
        );
    }
}
