<?php

use yii\db\Migration;

/**
 * Class m200202_035412_alter_text_column_tg_id_group_chats_table
 */
class m200202_035412_alter_text_column_tg_id_group_chats_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('group_chats', 'tg_id', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('group_chats', 'tg_id', 'integer');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200202_035412_alter_text_column_tg_id_group_chats_table cannot be reverted.\n";

        return false;
    }
    */
}
