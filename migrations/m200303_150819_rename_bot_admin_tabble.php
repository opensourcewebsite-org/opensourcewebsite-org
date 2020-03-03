<?php

use yii\db\Migration;

/**
 * Class m200303_150819_rename_bot_admin_tabble
 */
class m200303_150819_rename_bot_admin_tabble extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('{{%bot_admin}}', '{{%bot_chat_member}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('{{%bot_chat_member}}', '{{%bot_admin}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200303_150819_rename_bot_admin_tabble cannot be reverted.\n";

        return false;
    }
    */
}
