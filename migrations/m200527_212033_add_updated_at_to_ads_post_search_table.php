<?php

use yii\db\Migration;

/**
 * Class m200527_212033_add_updated_at_to_ads_post_search_table
 */
class m200527_212033_add_updated_at_to_ads_post_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post_search}}', 'updated_at', $this->integer()->unsigned()->notNull()->after('location_longitude'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ads_post_search}}', 'updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200527_212033_add_updated_at_to_ads_post_search_table cannot be reverted.\n";

        return false;
    }
    */
}
