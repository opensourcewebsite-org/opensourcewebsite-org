<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency_exchange_order_cash}}`.
 */
class m200317_092229_create_currency_exchange_order_cash_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('currency_exchange_order_cash', [
            'id' => $this->primaryKey()->unsigned(),
            'order_id' => $this->integer()->unsigned()->notNull(),
            'location_lat' => $this->string(255),
            'location_lon' => $this->string(255),
            'location_at' => $this->integer()->unsigned(),
            'delivery_status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'delivery_km' => $this->smallInteger()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('currency_exchange_order_cash');
    }
}
