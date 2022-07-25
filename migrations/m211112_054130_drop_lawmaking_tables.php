<?php

use yii\db\Migration;

/**
 * Class m211112_054130_drop_lawmaking_tables
 */
class m211112_054130_drop_lawmaking_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%bot_ua_lawmaking_vote}}');
        $this->dropTable('{{%bot_ua_lawmaking_voting}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211112_054130_drop_lawmaking_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211112_054130_delete_lawmaking_tables cannot be reverted.\n";

        return false;
    }
    */
}
