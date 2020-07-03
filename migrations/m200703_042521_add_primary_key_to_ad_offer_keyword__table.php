<?php

use yii\db\Migration;

/**
 * Class m200703_042521_add_primary_key_to_ad_offer_keyword__table
 */
class m200703_042521_add_primary_key_to_ad_offer_keyword__table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ad_offer_keyword}}', 'id', $this->primaryKey()->unsigned()->first());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ad_offer_keyword}}', 'id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200703_042521_add_primary_key_to_ad_offer_keyword__table cannot be reverted.\n";

        return false;
    }
    */
}
