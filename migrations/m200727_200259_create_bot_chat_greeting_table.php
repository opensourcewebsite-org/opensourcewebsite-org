<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_greeting}}`.
 */
class m200727_200259_create_bot_chat_greeting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_greeting}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'provider_user_id' => $this->integer()->unsigned()->notNull(),
            'sent_at' => $this->integer()->unsigned()->notNull(),
            'message_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_greeting-chat_id',
            '{{%bot_chat_greeting}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_greeting-provider_user_id',
            '{{%bot_chat_greeting}}',
            'provider_user_id',
            '{{%bot_user}}',
            'provider_user_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bot_chat_greeting-provider_user_id', '{{%bot_chat_greeting}}');
        $this->dropForeignKey('fk-bot_chat_greeting-chat_id', '{{%bot_chat_greeting}}');
        $this->dropTable('{{%bot_chat_greeting}}');
    }
}
