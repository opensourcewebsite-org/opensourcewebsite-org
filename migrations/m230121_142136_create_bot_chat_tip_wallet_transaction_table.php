<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_tip_wallet_transaction}}`.
 */
class m230121_142136_create_bot_chat_tip_wallet_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
}
