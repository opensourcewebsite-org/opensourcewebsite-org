<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_offer_keyword}}`.
 */
class m200424_202421_create_ad_offer_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_offer_keyword}}', [
            'ad_offer_id' => $this->integer()->unsigned()->notNull(),
            'ad_keyword_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_offer_keyword_ad_offer_id-ad_offer_id',
            '{{%ad_offer_keyword}}',
            'ad_offer_id',
            '{{%ad_offer}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_offer_keyword_ad_keyword_id-ad_keyword_id',
            '{{%ad_offer_keyword}}',
            'ad_keyword_id',
            '{{%ad_keyword}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_offer_keyword_ad_offer_id-ad_offer_id', '{{%ad_offer_keyword}}');

        $this->dropForeignKey('fk-ad_offer_keyword_ad_keyword_id-ad_keyword_id', '{{%ad_offer_keyword}}');

        $this->dropTable('{{%ad_offer_keyword}}');
    }
}
