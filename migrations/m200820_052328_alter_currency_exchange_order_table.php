<?php

use yii\db\Migration;

/**
 * Class m200820_052328_alter_currency_exchange_order_table
 */
class m200820_052328_alter_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('currency_exchange_order', 'delivery_radius', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('currency_exchange_order', 'delivery_radius', $this->integer()->unsigned()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200820_052328_alter_currency_exchange_order_table cannot be reverted.\n";

        return false;
    }
    */
}
