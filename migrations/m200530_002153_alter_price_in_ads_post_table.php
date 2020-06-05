<?php

use yii\db\Migration;

/**
 * Class m200530_002153_alter_price_in_ads_post_table
 */
class m200530_002153_alter_price_in_ads_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%ads_post}}', 'price', $this->integer()->unsigned()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200530_002153_alter_price_in_ads_post_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200530_002153_alter_price_in_ads_post_table cannot be reverted.\n";

        return false;
    }
    */
}
