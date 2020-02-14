<?php

use yii\db\Migration;

/**
 * Class m200213_175832_add_chat_id_to_filter_message_table
 */
class m200213_175832_add_chat_id_to_filter_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_message_filter}}', 'chat_id', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200213_175832_add_chat_id_to_filter_message_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200213_175832_add_chat_id_to_filter_message_table cannot be reverted.\n";

        return false;
    }
    */
}
