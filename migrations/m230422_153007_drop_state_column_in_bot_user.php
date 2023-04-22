<?php

use yii\db\Migration;

/**
 * Class m230422_153007_drop_state_column_in_bot_user
 */
class m230422_153007_drop_state_column_in_bot_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%bot_user}}', 'state');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%bot_user}}', 'state', $this->json());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230422_153007_drop_state_column_in_bot_user cannot be reverted.\n";

        return false;
    }
    */
}
