<?php

use yii\db\Migration;

/**
 * Class m200604_234252_rename_min_price_in_ads_post_search_table
 */
class m200604_234252_rename_min_price_in_ads_post_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%ads_post_search}}', 'min_price', 'max_price');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{%ads_post_search}}', 'max_price', 'min_price');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200604_234252_rename_min_price_in_ads_post_search_table cannot be reverted.\n";

        return false;
    }
    */
}
