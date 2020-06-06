<?php

use yii\db\Migration;

/**
 * Class m200523_004743_add_status_to_ads_post_search_table
 */
class m200523_004743_add_status_to_ads_post_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post_search}}', 'status', $this->string()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ads_post_search}}', 'status');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200523_004743_add_status_to_ads_post_search_table cannot be reverted.\n";

        return false;
    }
    */
}
