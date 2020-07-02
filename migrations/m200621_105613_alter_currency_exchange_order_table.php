<?php

use yii\db\Migration;

/**
 * Class m200621_105613_alter_currency_exchange_order_table
 */
class m200621_105613_alter_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('currency_exchange_order', 'delivery_radius', $this->integer()->unsigned()->notNull());
        $this->addColumn('currency_exchange_order', 'location_lat', $this->string()->notNull());
        $this->addColumn('currency_exchange_order', 'location_lon', $this->string()->notNull());
        $this->addColumn('currency_exchange_order', 'created_at', $this->integer()->unsigned()->notNull());
        $this->addColumn('currency_exchange_order', 'processed_at', $this->integer()->unsigned());
        $this->addColumn('currency_exchange_order', 'selling_cash_on', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('currency_exchange_order', 'buying_cash_on', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));

        $this->dropTable('currency_exchange_order_cash');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('currency_exchange_order', 'delivery_radius');
        $this->dropColumn('currency_exchange_order', 'location_lat');
        $this->dropColumn('currency_exchange_order', 'location_lon');
        $this->dropColumn('currency_exchange_order', 'created_at');
        $this->dropColumn('currency_exchange_order', 'processed_at');
        $this->dropColumn('currency_exchange_order', 'selling_cash_on');
        $this->dropColumn('currency_exchange_order', 'buying_cash_on');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200621_105613_alter_currency_exchange_order_table cannot be reverted.\n";

        return false;
    }
    */
}
