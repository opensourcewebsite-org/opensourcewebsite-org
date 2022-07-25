<?php

use yii\db\Migration;

/**
 * Class m201002_104816_remove_bot_route_alias_table
 */
class m201002_104816_remove_bot_route_alias_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-bot_route_alias-chat_id', '{{%bot_route_alias}}');
        $this->dropTable('{{%bot_route_alias}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201002_104816_remove_bot_route_alias_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201002_104816_remove_bot_route_alias_table cannot be reverted.\n";

        return false;
    }
    */
}
