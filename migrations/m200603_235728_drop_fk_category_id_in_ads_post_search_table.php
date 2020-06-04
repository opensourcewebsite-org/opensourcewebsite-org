<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%fk_category_id_in_ads_post_search}}`.
 */
class m200603_235728_drop_fk_category_id_in_ads_post_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-ads_post_search_category_id-bot_ad_category_id', '{{%ads_post_search}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addForeignKey(
            'fk-ads_post_search_category_id-bot_ad_category_id',
            '{{%ads_post_search}}',
            'category_id',
            '{{%bot_ad_category}}',
            'id'
        );
    }
}
