<?php

use yii\db\Migration;

/**
 * Class m200219_173204_rename_is_email_confirmed_to_authenticated_in_user_table
 */
class m200219_173204_rename_is_email_confirmed_to_is_authenticated_in_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%user}}', 'is_email_confirmed', 'is_authenticated');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{%user}}', 'is_authenticated', 'is_email_confirmed');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200219_173204_rename_is_email_confirmed_to_is_user_authenticated_in_user_table cannot be reverted.\n";

        return false;
    }
    */
}
