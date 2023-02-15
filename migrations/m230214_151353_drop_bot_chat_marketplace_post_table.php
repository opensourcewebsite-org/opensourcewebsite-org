<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%bot_chat_marketplace_post}}`.
 */
class m230214_151353_drop_bot_chat_marketplace_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-bot_chat_marketplace_post-member_id',
            '{{%bot_chat_marketplace_post}}'
        );

        $this->dropTable('{{%bot_chat_marketplace_post}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%bot_chat_marketplace_post}}', [
            'id' => $this->primaryKey()->unsigned(),
            'member_id' => $this->integer()->unsigned()->notNull(),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'title' => $this->string(),
            'text' => $this->text()->notNull(),
            'time' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'skip_days' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'next_send_at' => $this->integer()->unsigned(),
            'sent_at' => $this->integer()->unsigned(),
            'provider_message_id' => $this->integer()->unsigned(),
            'processed_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_marketplace_post-member_id',
            '{{%bot_chat_marketplace_post}}',
            'chat_id',
            '{{%bot_chat_member}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
