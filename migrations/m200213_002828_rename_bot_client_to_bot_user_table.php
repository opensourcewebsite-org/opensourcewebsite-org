<?php

use yii\db\Migration;

/**
 * Class m200213_002828_rename_bot_client_to_bot_user_table
 */
class m200213_002828_rename_bot_client_to_bot_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('bot_client', 'bot_user');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('bot_user', 'bot_client');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200213_002828_rename_bot_client_to_bot_user_table cannot be reverted.\n";

        return false;
    }
    */
}
