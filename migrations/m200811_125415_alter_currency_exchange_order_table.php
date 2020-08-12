<?php

use yii\db\Migration;

/**
 * Class m200811_125415_alter_currency_exchange_order_table
 */
class m200811_125415_alter_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('currency_exchange_order', 'location_lat', $this->string());
        $this->alterColumn('currency_exchange_order', 'location_lon', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('currency_exchange_order', 'location_lat', $this->string()->notNull());
        $this->alterColumn('currency_exchange_order', 'location_lon', $this->string()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200811_125415_alter_currency_exchange_order cannot be reverted.\n";

        return false;
    }
    */
}
