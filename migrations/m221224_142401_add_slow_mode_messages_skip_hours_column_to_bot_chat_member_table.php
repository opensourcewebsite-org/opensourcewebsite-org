<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat_member}}`.
 */
class m221224_142401_add_slow_mode_messages_skip_hours_column_to_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat_member}}', 'slow_mode_messages_skip_hours', $this->smallInteger()->unsigned()->after('slow_mode_messages_skip_days'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat_member}}', 'slow_mode_messages_skip_hours');
    }
}
