<?php

use yii\db\Migration;

/**
 * Class m200303_153148_rename_bot_setting_table
 */
class m200303_153148_rename_bot_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('{{%bot_setting}}', '{{%bot_chat_setting}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('{{%bot_chat_setting}}', '{{%bot_setting}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200303_153148_rename_bot_setting_table cannot be reverted.\n";

        return false;
    }
    */
}
