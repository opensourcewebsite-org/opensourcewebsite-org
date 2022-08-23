<?php

use yii\db\Migration;

/**
 * Class m220818_105648_alter_bot_chat_marketplace_post_table
 */
class m220818_105648_alter_bot_chat_marketplace_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%bot_chat_marketplace_post}}', 'updated_at', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{%bot_chat_marketplace_post}}', 'created_at', 'updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220818_105648_alter_bot_chat_marketplace_post_table cannot be reverted.\n";

        return false;
    }
    */
}
