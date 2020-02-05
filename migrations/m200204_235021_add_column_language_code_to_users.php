<?php

use yii\db\Migration;

/**
 * Class m200204_235021_add_column_language_code_to_users
 */
class m200204_235021_add_column_language_code_to_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%group_user}}', 'language_code', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%group_user}}', 'language_code');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200204_235021_add_column_language_code_to_users cannot be reverted.\n";

        return false;
    }
    */
}
