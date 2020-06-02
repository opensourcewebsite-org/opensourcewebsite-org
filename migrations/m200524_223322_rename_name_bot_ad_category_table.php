<?php

use yii\db\Migration;

/**
 * Class m200524_223322_rename_name_bot_ad_category_table
 */
class m200524_223322_rename_name_bot_ad_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%bot_ad_category}}', 'name', 'find_name');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{%bot_ad_category}}', 'find_name', 'name');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200524_223322_rename_name_bot_ad_category_table cannot be reverted.\n";

        return false;
    }
    */
}
