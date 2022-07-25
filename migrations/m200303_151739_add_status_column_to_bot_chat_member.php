<?php

use yii\db\Migration;

/**
 * Class m200303_151739_add_status_column_to_bot_chat_member
 */
class m200303_151739_add_status_column_to_bot_chat_member extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat_member}}', 'status', $this->string()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat_memnber}}', 'status');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200303_151739_add_status_column_to_bot_chat_member cannot be reverted.\n";

        return false;
    }
    */
}
