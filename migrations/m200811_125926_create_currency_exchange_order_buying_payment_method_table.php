<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency_exchange_order_buying_payment_method}}`.
 */
class m200811_125926_create_currency_exchange_order_buying_payment_method_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%currency_exchange_order_buying_payment_method}}', [
            'id' => $this->primaryKey()->unsigned(),
            'order_id' => $this->integer()->unsigned()->notNull(),
            'payment_method_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-currency_exchange_order_buying_payment_method-order_id',
            '{{%currency_exchange_order_buying_payment_method}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-currency_exchange_order_buying_payment_method-p_m_id',
            '{{%currency_exchange_order_buying_payment_method}}',
            'payment_method_id',
            '{{%payment_method}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-currency_exchange_order_buying_payment_method-order_id', '{{%currency_exchange_order_buying_payment_method}}');

        $this->dropForeignKey('fk-currency_exchange_order_buying_payment_method-p_m_id', '{{%currency_exchange_order_buying_payment_method}}');

        $this->dropTable('{{%currency_exchange_order_buying_payment_method}}');
    }
}
