<?php

use yii\db\Migration;

/**
 * Class m210604_110130_alter_foreign_keys_to_ad_search_keyword
 */
class m210604_110130_alter_foreign_keys_to_ad_search_keyword extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-ad_search_keyword_ad_search_id-ad_search_id', '{{%ad_search_keyword}}');
        $this->dropForeignKey('fk-ad_search_keyword_ad_keyword_id-ad_keyword_id', '{{%ad_search_keyword}}');

        $this->addForeignKey(
            'fk-ad_search_keyword_ad_search_id-ad_search_id',
            '{{%ad_search_keyword}}',
            'ad_search_id',
            '{{%ad_search}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-ad_search_keyword_ad_keyword_id-ad_keyword_id',
            '{{%ad_search_keyword}}',
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
        $this->dropForeignKey('fk-ad_search_keyword_ad_search_id-ad_search_id', '{{%ad_search_keyword}}');
        $this->dropForeignKey('fk-ad_search_keyword_ad_keyword_id-ad_keyword_id', '{{%ad_search_keyword}}');

        $this->addForeignKey(
            'fk-ad_search_keyword_ad_search_id-ad_search_id',
            '{{%ad_search_keyword}}',
            'ad_search_id',
            '{{%ad_search}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_search_keyword_ad_keyword_id-ad_keyword_id',
            '{{%ad_search_keyword}}',
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
        echo "m210604_110130_alter_foreign_keys_to_ad_search_keyword cannot be reverted.\n";

        return false;
    }
    */
}
