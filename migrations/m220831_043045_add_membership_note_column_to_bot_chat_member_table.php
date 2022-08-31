<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat_member}}`.
 */
class m220831_043045_add_membership_note_column_to_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat_member}}', 'membership_note', $this->string()->after('membership_date'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat_member}}', 'membership_note');
    }
}
