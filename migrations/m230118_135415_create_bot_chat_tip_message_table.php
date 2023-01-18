<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_tip_message}}`.
 */
class m230118_135415_create_bot_chat_tip_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_tip_message}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'transaction_id' => $this->integer()->unsigned()->notNull(),
            'message_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_tip_message-chat_id',
            '{{%bot_chat_tip_message}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_tip_message-transaction_id',
            '{{%bot_chat_tip_message}}',
            'transaction_id',
            '{{%wallet_transaction}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_chat_tip_message-chat_id',
            '{{%bot_chat_tip_message}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_tip_message-transaction_id',
            '{{%bot_chat_tip_message}}'
        );

        $this->dropTable('{{%bot_chat_tip_message}}');
    }
}
