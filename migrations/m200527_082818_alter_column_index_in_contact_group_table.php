<?php

use yii\db\Migration;

/**
 * Class m200527_082818_alter_column_index_in_contact_group_table
 */
class m200527_082818_alter_column_index_in_contact_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('name', 'contact_group');
        $this->createIndex('uniq-idx-name-user-id', 'contact_group', ['name', 'user_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200527_082818_alter_column_index_in_contact_group_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200527_082818_alter_column_index_in_contact_group_table cannot be reverted.\n";

        return false;
    }
    */
}
