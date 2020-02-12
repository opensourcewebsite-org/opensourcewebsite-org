<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%bot_inside_message}}`.
 */
class m200204_165815_drop_bot_inside_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%bot_inside_message}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->createTable('{{%bot_inside_message}}', [
            'id' => $this->primaryKey(),
            'bot_id' => $this->integer()->unsigned()->notNull(),
            'bot_client_id' => $this->integer()->unsigned(),
            'provider_chat_id' => $this->bigInteger()->unsigned(),
            'message' => $this->text()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            '{{%fk-bot_inside_message-bot}}',
            '{{%bot_inside_message}}',
            'bot_id',
            '{{%bot}}',
            'id',
            'CASCADE');
        $this->addForeignKey(
            '{{%fk-bot_inside_message-bot_client}}',
            '{{%bot_inside_message}}',
            'bot_client_id',
            '{{%bot_client}}',
            'id',
            'CASCADE');
    }
}
