<?php

use app\models\WalletTransaction;
use yii\db\Migration;
use yii\db\Query;

/**
 * Handles the dropping of table `{{%bot_chat_tip_wallet_transaction}}`.
 */
class m230504_012321_drop_bot_chat_tip_wallet_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-bot_chat_tip_wallet_transaction-chat_tip_id',
            '{{%bot_chat_tip_wallet_transaction}}'
        );
        $this->dropForeignKey(
            'fk-bot_chat_tip_wallet_transaction-transaction_id',
            '{{%bot_chat_tip_wallet_transaction}}'
        );

        $this->dropTable('{{%bot_chat_tip_wallet_transaction}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%bot_chat_tip_wallet_transaction}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_tip_id' => $this->integer()->unsigned()->notNull(),
            'transaction_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_tip_wallet_transaction-chat_tip_id',
            '{{%bot_chat_tip_wallet_transaction}}',
            'chat_tip_id',
            '{{%bot_chat_tip}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_tip_wallet_transaction-transaction_id',
            '{{%bot_chat_tip_wallet_transaction}}',
            'transaction_id',
            '{{%wallet_transaction}}',
            'id'
        );

        $rows = (new Query())
            ->select([
                'ct.id as chat_tip_id',
                't.id as transaction_id',
            ])
            ->from('wallet_transaction t')
            ->join('JOIN', 'bot_chat_tip ct', '(t.data ->> :chatTipIdKey) = ct.id', [
                ':chatTipIdKey' => '$.' . WalletTransaction::CHAT_TIP_ID_DATA_KEY,
            ]);

        foreach ($rows->each(1) as $row) {
            $this->insert(
                '{{%bot_chat_tip_wallet_transaction}}',
                [
                    'chat_tip_id' => $row['chat_tip_id'],
                    'transaction_id' => $row['transaction_id'],
                ]
            );
        }
    }
}
