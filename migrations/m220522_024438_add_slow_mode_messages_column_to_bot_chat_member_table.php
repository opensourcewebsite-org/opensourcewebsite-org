<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat_member}}`.
 */
class m220522_024438_add_slow_mode_messages_column_to_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat_member}}', 'slow_mode_messages', $this->integer()->unsigned()->notNull()->defaultValue(0)->after('role'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat_member}}', 'slow_mode_messages');
    }
}
