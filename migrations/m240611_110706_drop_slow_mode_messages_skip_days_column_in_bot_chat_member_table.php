<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%slow_mode_messages_skip_days_column_to_bot_chat_member}}`.
 */
class m240611_110706_drop_slow_mode_messages_skip_days_column_in_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%bot_chat_member}}', 'slow_mode_messages_skip_days');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%bot_chat_member}}', 'slow_mode_messages_skip_days', $this->smallInteger()->unsigned()->after('slow_mode_messages_limit'));
    }
}
