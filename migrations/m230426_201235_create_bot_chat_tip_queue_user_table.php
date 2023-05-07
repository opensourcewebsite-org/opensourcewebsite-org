<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_tip_queue_user}}`.
 */
class m230426_201235_create_bot_chat_tip_queue_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_tip_queue_user}}', [
            'id' => $this->primaryKey()->unsigned(),
            'queue_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'transaction_id' => $this->integer()->unsigned(),
        ]);

        $this->createIndex(
            'idx-bot_chat_tip_queue_user-queue_id-user_id',
            '{{%bot_chat_tip_queue_user}}',
            ['queue_id', 'user_id'],
            true
        );

        $this->addForeignKey(
            'fk-bot_chat_tip_queue_user-queue_id',
            '{{%bot_chat_tip_queue_user}}',
            'queue_id',
            '{{%bot_chat_tip_queue}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_tip_queue_user-user_id',
            '{{%bot_chat_tip_queue_user}}',
            'user_id',
            '{{%bot_user}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_tip_queue_user-transaction_id',
            '{{%bot_chat_tip_queue_user}}',
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
            'fk-bot_chat_tip_queue_user-transaction_id',
            '{{%bot_chat_tip_queue_user}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_tip_queue_user-user_id',
            '{{%bot_chat_tip_queue_user}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_tip_queue_user-queue_id',
            '{{%bot_chat_tip_queue_user}}'
        );

        $this->dropIndex('idx-bot_chat_tip_queue_user-queue_id-user_id', '{{%bot_chat_tip_queue_user}}');

        $this->dropTable('{{%bot_chat_tip_queue_user}}');
    }
}
