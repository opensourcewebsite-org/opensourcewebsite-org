<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_search_match}}`.
 */
class m200806_040228_create_ad_search_match_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_search_match}}', [
            'id' => $this->primaryKey()->unsigned(),
            'ad_search_id' => $this->integer()->unsigned()->notNull(),
            'ad_offer_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_search_match_ad_offer_id-ad_offer_id',
            '{{%ad_search_match}}',
            'ad_offer_id',
            '{{%ad_offer}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-ad_search_match_ad_search_id-ad_search_id',
            '{{%ad_search_match}}',
            'ad_search_id',
            '{{%ad_search}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_search_match_ad_search_id-ad_search_id', '{{%ad_search_match}}');

        $this->dropForeignKey('fk-ad_search_match_ad_offer_id-ad_offer_id', '{{%ad_search_match}}');

        $this->dropTable('{{%ad_search_match}}');
    }
}
