<?php

use yii\db\Migration;

/**
 * Class m220821_115022_rename_bot_phrase_table
 */
class m220821_115022_rename_bot_phrase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('{{%bot_phrase}}', '{{%bot_chat_phrase}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('{{%bot_chat_phrase}}', '{{%bot_phrase}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220821_115022_rename_bot_phrase_table cannot be reverted.\n";

        return false;
    }
    */
}
