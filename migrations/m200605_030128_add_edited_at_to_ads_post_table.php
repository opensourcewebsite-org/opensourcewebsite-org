<?php

use yii\db\Migration;

/**
 * Class m200605_030128_add_edited_at_to_ads_post_table
 */
class m200605_030128_add_edited_at_to_ads_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post}}', 'edited_at', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ads_post}}', 'edited_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200605_030128_add_edited_at_to_ads_post_table cannot be reverted.\n";

        return false;
    }
    */
}
