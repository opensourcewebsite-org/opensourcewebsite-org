<?php

use yii\db\Migration;

/**
 * Class m201002_060206_remove_bot_voteban_tables
 */
class m201002_060206_remove_bot_voteban_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('{{%bot_route_alias}}');

        $this->dropForeignKey('fk-bot_voteban_voting-chat_id', '{{%bot_voteban_voting}}');
        $this->dropTable('{{%bot_voteban_voting}}');

        $this->dropForeignKey('fk-bot_voteban_vote-chat_id', '{{%bot_voteban_vote}}');
        $this->dropTable('{{%bot_voteban_vote}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201002_060206_remove_bot_voteban_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201002_060206_remove_bot_voteban_tables cannot be reverted.\n";

        return false;
    }
    */
}
