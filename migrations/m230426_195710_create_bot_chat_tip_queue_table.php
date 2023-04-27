<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_tip_queue}}`.
 */
class m230426_195710_create_bot_chat_tip_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_tip_queue}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'message_id' => $this->integer()->unsigned(),
            'user_count' => $this->integer()->unsigned()->defaultValue(1),
            'user_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_tip_queue-chat_id',
            '{{%bot_chat_tip_queue}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_tip_queue-currency_id',
            '{{%bot_chat_tip_queue}}',
            'currency_id',
            '{{%currency}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_chat_tip_queue-currency_id',
            '{{%bot_chat_tip_queue}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_tip_queue-chat_id',
            '{{%bot_chat_tip_queue}}'
        );

        $this->dropTable('{{%bot_chat_tip_queue}}');
    }
}
