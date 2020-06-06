<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_matches}}`.
 */
class m200605_024930_create_ad_matches_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_matches}}', [
            'id' => $this->primaryKey()->unsigned(),
            'ads_post_id' => $this->integer()->unsigned()->notNull(),
            'ads_post_search_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_matches_ads_post_id-ads_post_id',
            '{{%ad_matches}}',
            'ads_post_id',
            '{{%ads_post}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_matches_ads_post_search_id-ads_post_search_id',
            '{{%ad_matches}}',
            'ads_post_search_id',
            '{{%ads_post_search}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_matches_ads_post_search_id-ads_post_search_id');

        $this->dropForeignKey('fk-ad_matches_ads_post_id-ads_post_id');

        $this->dropTable('{{%ad_matches}}');
    }
}
