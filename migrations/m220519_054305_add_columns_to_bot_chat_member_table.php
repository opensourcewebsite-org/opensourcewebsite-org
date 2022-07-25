<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat_member}}`.
 */
class m220519_054305_add_columns_to_bot_chat_member_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat_member}}', 'last_message_at', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat_member}}', 'last_message_at');
    }
}
