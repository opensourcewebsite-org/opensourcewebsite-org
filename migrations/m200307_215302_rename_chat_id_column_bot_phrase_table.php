<?php

use yii\db\Migration;

/**
 * Class m200307_215302_rename_chat_id_column_bot_phrase_table
 */
class m200307_215302_rename_chat_id_column_bot_phrase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%bot_phrase}}', 'group_id', 'chat_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{%bot_phrase}}', 'chat_id', 'group_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200307_215302_rename_chat_id_column_bot_phrase_table cannot be reverted.\n";

        return false;
    }
    */
}
