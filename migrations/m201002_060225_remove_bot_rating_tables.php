<?php

use yii\db\Migration;

/**
 * Class m201002_060225_remove_bot_rating_tables
 */
class m201002_060225_remove_bot_rating_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-bot_rating_voting-chat_id', '{{%bot_rating_voting}}');
        $this->dropTable('{{%bot_rating_voting}}');

        $this->dropForeignKey('fk-bot_rating_vote-chat_id', '{{%bot_rating_vote}}');
        $this->dropTable('{{%bot_rating_vote}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201002_060225_remove_bot_rating_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201002_060225_remove_bot_rating_tables cannot be reverted.\n";

        return false;
    }
    */
}
