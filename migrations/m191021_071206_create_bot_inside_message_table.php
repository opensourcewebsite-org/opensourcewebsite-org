<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_inside_message}}`.
 */
class m191021_071206_create_bot_inside_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_inside_message}}', [
            'id' => $this->primaryKey(),
            'bot_id' => $this->integer()->unsigned()->notNull(),
            'bot_client_id' => $this->integer()->unsigned(),
            'provider_chat_id' => $this->bigInteger()->unsigned(),
            'message' => $this->text()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey('{{%fk-bot_inside_message-bot}}', '{{%bot_inside_message}}',
            'bot_id', '{{%bot}}', 'id', 'CASCADE');
        $this->addForeignKey('{{%fk-bot_inside_message-bot_client}}', '{{%bot_inside_message}}',
            'bot_client_id', '{{%bot_client}}', 'id', 'CASCADE');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bot_inside_message}}');
    }
}
