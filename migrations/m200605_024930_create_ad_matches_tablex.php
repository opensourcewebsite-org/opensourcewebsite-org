<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_matches}}`.
 */
class m200605_024930_create_ad_matches_tablex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_matches}}', [
            'id' => $this->primaryKey()->unsigned(),
            'ad_order_id' => $this->integer()->unsigned()->notNull(),
            'ad_search_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_matches_ad_order_id-ad_order_id',
            '{{%ad_matches}}',
            'ad_order_id',
            '{{%ad_order}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_matches_ad_search_id-ad_search_id',
            '{{%ad_matches}}',
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
        $this->dropForeignKey('fk-ad_matches_ad_search_id-ad_search_id');

        $this->dropForeignKey('fk-ad_matches_ad_order_id-ad_order_id');

        $this->dropTable('{{%ad_matches}}');
    }
}
