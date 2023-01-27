<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_tip}}`.
 */
class m230121_141926_create_bot_chat_tip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_tip}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'message_id' => $this->integer()->unsigned(),
            'sent_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_tip-chat_id',
            '{{%bot_chat_tip}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_chat_tip-chat_id',
            '{{%bot_chat_tip}}'
        );

        $this->dropTable('{{%bot_chat_tip}}');
    }
}
