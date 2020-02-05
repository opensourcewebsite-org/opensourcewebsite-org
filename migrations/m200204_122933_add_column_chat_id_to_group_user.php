<?php

use yii\db\Migration;

/**
 * Class m200204_122933_add_column_chat_id_to_group_user
 */
class m200204_122933_add_column_chat_id_to_group_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%group_user}}', 'chat_id', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%group_user}}', 'chat_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200204_122933_add_column_chat_id_to_group_user cannot be reverted.\n";

        return false;
    }
    */
}
