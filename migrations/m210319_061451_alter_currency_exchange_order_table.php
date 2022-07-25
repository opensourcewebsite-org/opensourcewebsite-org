<?php

use yii\db\Migration;

/**
 * Class m210319_061451_alter_currency_exchange_order_table
 */
class m210319_061451_alter_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%currency_exchange_order}}', 'delivery_radius', 'selling_delivery_radius');
        $this->renameColumn('{{%currency_exchange_order}}', 'location_lat', 'selling_location_lat');
        $this->renameColumn('{{%currency_exchange_order}}', 'location_lon', 'selling_location_lon');

        $this->addColumn('{{%currency_exchange_order}}', 'buying_delivery_radius', $this->integer()->unsigned()->after('buying_cash_on'));
        $this->addColumn('{{%currency_exchange_order}}', 'buying_location_lat', $this->string()->after('buying_delivery_radius'));
        $this->addColumn('{{%currency_exchange_order}}', 'buying_location_lon', $this->string()->after('buying_location_lat'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%currency_exchange_order}}', 'buying_delivery_radius');
        $this->dropColumn('{{%currency_exchange_order}}', 'buying_location_lat');
        $this->dropColumn('{{%currency_exchange_order}}', 'buying_location_lon');

        $this->renameColumn('{{%currency_exchange_order}}', 'selling_delivery_radius', 'delivery_radius');
        $this->renameColumn('{{%currency_exchange_order}}', 'selling_location_lat', 'location_lat');
        $this->renameColumn('{{%currency_exchange_order}}', 'selling_location_lon', 'location_lon');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210319_061451_alter_currency_exchange_order_table cannot be reverted.\n";
        return false;
    }
    */
}
