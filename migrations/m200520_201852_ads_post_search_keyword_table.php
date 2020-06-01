<?php

use yii\db\Migration;

/**
 * Class m200520_201852_ads_post_search_keyword_table
 */
class m200520_201852_ads_post_search_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ads_post_search_keyword}}', [
            'ads_post_search_id' => $this->integer()->unsigned()->notNull(),
            'keyword_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ads_post_search_keyword_ads_post_search_id-ads_post_search_id',
            '{{%ads_post_search_keyword}}',
            'ads_post_search_id',
            '{{%ads_post_search}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ads_post_search_keyword_id-bot_ad_keyword_id',
            '{{%ads_post_search_keyword}}',
            'keyword_id',
            '{{%bot_ad_keyword}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ads_post_search_keyword_id-bot_ad_keyword_id');

        $this->dropForeignKey('fk-ads_post_search_keyword_ads_post_search_id-ads_post_search_id');

        $this->dropTable('{{%ads_post_search_keyword}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200520_201852_ads_post_search_keyword_table cannot be reverted.\n";

        return false;
    }
    */
}
