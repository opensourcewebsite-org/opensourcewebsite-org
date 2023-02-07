<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_publisher_post}}`.
 */
class m230205_133014_create_bot_chat_publisher_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_publisher_post}}', [
            'id' => $this->primaryKey()->unsigned(),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'title' => $this->string(),
            'text' => $this->text()->notNull(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'time' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'skip_days' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'sent_at' => $this->integer()->unsigned(),
            'next_sent_at' => $this->integer()->unsigned(),
            'provider_message_id' => $this->integer()->unsigned(),
            'processed_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_publisher_post-chat_id',
            '{{%bot_chat_publisher_post}}',
            'chat_id',
            '{{%bot_chat}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_chat_publisher_post-chat_id',
            '{{%bot_chat_publisher_post}}'
        );

        $this->dropTable('{{%bot_chat_marketplace_post}}');
    }
}
