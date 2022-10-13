<?php

use yii\db\Migration;

/**
 * Class m221005_074220_add_slow_mode_messages_skip_days_column_to_bot_chat_member_table
 */
class m221005_074220_add_slow_mode_messages_skip_days_column_to_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat_member}}', 'slow_mode_messages_skip_days', $this->smallInteger()->unsigned()->after('slow_mode_messages_limit'));

        $this->alterColumn('{{%bot_chat_member}}', 'slow_mode_messages_limit', $this->smallInteger()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat_member}}', 'slow_mode_messages_skip_days');

        $this->alterColumn('{{%bot_chat_member}}', 'slow_mode_messages_limit', $this->smallInteger());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221005_074220_alter_slow_mode_messages_column_in_bot_chat_member_table cannot be reverted.\n";

        return false;
    }
    */
}
