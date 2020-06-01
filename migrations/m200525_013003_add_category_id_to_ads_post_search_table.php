<?php

use yii\db\Migration;

/**
 * Class m200525_013003_add_category_id_to_ads_post_search_table
 */
class m200525_013003_add_category_id_to_ads_post_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post_search}}', 'category_id', $this->integer()->unsigned()->notNull()->after('user_id'));

        $this->addForeignKey(
            'fk-ads_post_search_category_id-bot_ad_category_id',
            '{{%ads_post_search}}',
            'category_id',
            '{{%bot_ad_category}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ads_post_search_category_id-bot_ad_category_id');

        $this->dropColumn('{{%ads_post_search}}', 'category_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200525_013003_add_category_id_to_ads_post_search_table cannot be reverted.\n";

        return false;
    }
    */
}
