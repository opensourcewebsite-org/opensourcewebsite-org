<?php

use yii\db\Migration;

/**
 * Class m200520_201852_create_ad_search_keyword_table
 */
class m200520_201852_create_ad_search_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_search_keyword}}', [
            'ad_search_id' => $this->integer()->unsigned()->notNull(),
            'ad_keyword_id' => $this->integer()->unsigned()->notNull(),
        ]);

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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_search_keyword_ad_search_id-ad_search_id', '{{%ad_search_keyword}}');

        $this->dropForeignKey('fk-ad_search_keyword_ad_keyword_id-ad_keyword_id', '{{%ad_search_keyword}}');

        $this->dropTable('{{%ad_search_keyword}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200520_201852_create_ad_search_keyword_table cannot be reverted.\n";

        return false;
    }
    */
}
