<?php

use yii\db\Migration;

/**
 * Class m200806_171105_alter_currency_exchange_order_table
 */
class m200806_171105_alter_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(
            'currency_exchange_order',
            'location_lat',
            $this->string(255)->defaultValue(null)
        );
        $this->update('currency_exchange_order', ['location_lat' => null], 'location_lat = ""');
        $this->alterColumn(
            'currency_exchange_order',
            'location_lon',
            $this->string(255)->defaultValue(null)
        );
        $this->update('currency_exchange_order', ['location_lon' => null], 'location_lon = ""');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn(
            'currency_exchange_order',
            'location_lat',
            $this->string(255)->notNull()
        );
        $this->addColumn(
            'currency_exchange_order',
            'location_lon',
            $this->string(255)->notNull()
        );
    }
}
