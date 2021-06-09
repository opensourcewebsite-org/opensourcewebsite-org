<?php

use yii\db\Migration;

/**
 * Class m210604_110123_alter_foreign_keys_to_ad_offer_keyword
 */
class m210604_110123_alter_foreign_keys_to_ad_offer_keyword extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-ad_offer_keyword_ad_offer_id-ad_offer_id', '{{%ad_offer_keyword}}');
        $this->dropForeignKey('fk-ad_offer_keyword_ad_keyword_id-ad_keyword_id', '{{%ad_offer_keyword}}');

        $this->addForeignKey(
            'fk-ad_offer_keyword_ad_offer_id-ad_offer_id',
            '{{%ad_offer_keyword}}',
            'ad_offer_id',
            '{{%ad_offer}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-ad_offer_keyword_ad_keyword_id-ad_keyword_id',
            '{{%ad_offer_keyword}}',
            'ad_keyword_id',
            '{{%ad_keyword}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_offer_keyword_ad_offer_id-ad_offer_id', '{{%ad_offer_keyword}}');
        $this->dropForeignKey('fk-ad_offer_keyword_ad_keyword_id-ad_keyword_id', '{{%ad_offer_keyword}}');

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

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210604_110123_alter_foreign_keys_to_ad_offer_keyword cannot be reverted.\n";

        return false;
    }
    */
}
