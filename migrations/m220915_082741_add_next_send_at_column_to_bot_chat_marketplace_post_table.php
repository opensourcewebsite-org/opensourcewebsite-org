<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat_marketplace_post}}`.
 */
class m220915_082741_add_next_send_at_column_to_bot_chat_marketplace_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat_marketplace_post}}', 'next_send_at', $this->integer()->unsigned()->after('created_at'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat_marketplace_post}}', 'next_send_at');
    }
}
