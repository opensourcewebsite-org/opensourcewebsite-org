<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_matches}}`.
 */
class m200605_024930_create_ad_match_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_match}}', [
            'id' => $this->primaryKey()->unsigned(),
            'ad_offer_id' => $this->integer()->unsigned()->notNull(),
            'ad_search_id' => $this->integer()->unsigned()->notNull(),
            'type' => $this->tinyInteger()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_match_ad_offer_id-ad_offer_id',
            '{{%ad_match}}',
            'ad_offer_id',
            '{{%ad_offer}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_match_ad_search_id-ad_search_id',
            '{{%ad_match}}',
            'ad_search_id',
            '{{%ad_search}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_match_ad_search_id-ad_search_id');

        $this->dropForeignKey('fk-ad_match_ad_offer_id-ad_offer_id');

        $this->dropTable('{{%ad_match}}');
    }
}
