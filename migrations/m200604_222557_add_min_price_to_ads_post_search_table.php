<?php

use yii\db\Migration;

/**
 * Class m200604_222557_add_min_price_to_ads_post_search_table
 */
class m200604_222557_add_min_price_to_ads_post_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post_search}}', 'min_price', $this->integer()->unsigned()->after('radius'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ads_post_search}}', 'min_price');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200604_222557_add_min_price_to_ads_post_search_table cannot be reverted.\n";

        return false;
    }
    */
}
