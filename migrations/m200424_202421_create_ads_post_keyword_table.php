<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ads_post_keyword}}`.
 */
class m200424_202421_create_ads_post_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ads_post_keyword}}', [
            'ads_post_id' => $this->integer()->unsigned()->notNull(),
            'keyword_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ads_post_keyword_ads_post_id-ads_post_id',
            '{{%ads_post_keyword}}',
            'ads_post_id',
            '{{%ads_post}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ads_post_keyword_keyword_id-bot_ad_keyword_id',
            '{{%ads_post_keyword}}',
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
        $this->dropForeignKey('fk-ads_post_keyword_keyword_id-bot_ad_keyowrd_id');

        $this->dropForeignKey('fk-ads_post_keyword_ads_post_id-ads_post_id');

        $this->dropTable('{{%ads_post_keyword}}');
    }
}
