<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_offer_match}}`.
 */
class m200806_040220_create_ad_offer_match_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-ad_match_ad_search_id-ad_search_id', '{{%ad_match}}');

        $this->dropForeignKey('fk-ad_match_ad_offer_id-ad_offer_id', '{{%ad_match}}');

        $this->dropTable('{{%ad_match}}');

        $this->createTable('{{%ad_offer_match}}', [
            'id' => $this->primaryKey()->unsigned(),
            'ad_offer_id' => $this->integer()->unsigned()->notNull(),
            'ad_search_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_offer_match_ad_offer_id-ad_offer_id',
            '{{%ad_offer_match}}',
            'ad_offer_id',
            '{{%ad_offer}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-ad_offer_match_ad_search_id-ad_search_id',
            '{{%ad_offer_match}}',
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
        $this->dropForeignKey('fk-ad_offer_match_ad_search_id-ad_search_id', '{{%ad_offer_match}}');

        $this->dropForeignKey('fk-ad_offer_match_ad_offer_id-ad_offer_id', '{{%ad_offer_match}}');

        $this->dropTable('{{%ad_offer_match}}');

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
}
