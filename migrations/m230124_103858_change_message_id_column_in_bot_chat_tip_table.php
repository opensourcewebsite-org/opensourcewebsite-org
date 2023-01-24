<?php

use yii\db\Migration;

/**
 * Class m230124_103858_change_message_id_column_in_bot_chat_tip_table
 */
class m230124_103858_change_message_id_column_in_bot_chat_tip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%bot_chat_tip}}', 'message_id', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%bot_chat_tip}}', 'message_id', $this->integer()->unsigned()->notNull());
    }

}
