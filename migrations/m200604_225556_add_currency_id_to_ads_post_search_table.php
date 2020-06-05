<?php

use yii\db\Migration;

/**
 * Class m200604_225556_add_currency_id_to_ads_post_search_table
 */
class m200604_225556_add_currency_id_to_ads_post_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post_search}}', 'currency_id', $this->integer()->unsigned()->after('radius'));

        $this->addForeignKey(
            'fk-ads_post_search_currency_id-currency_id',
            '{{%ads_post_search}}',
            'currency_id',
            '{{%currency}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ads_post_search_currency_id-currency_id');

        $this->dropColumn('{{%ads_post_search}}', 'currency_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200604_225556_add_currency_id_to_ads_post_search_table cannot be reverted.\n";

        return false;
    }
    */
}
