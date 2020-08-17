<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency_exchange_order_selling_payment_method}}`.
 */
class m200811_125916_create_currency_exchange_order_selling_payment_method_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('{{%currency_exchange_order_payment_method}}', '{{%currency_exchange_order_selling_payment_method}}');

        $this->dropColumn('{{%currency_exchange_order_selling_payment_method}}', 'type');

        $this->addForeignKey(
            'fk-currency_exchange_order_selling_payment_method-order_id',
            '{{%currency_exchange_order_selling_payment_method}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-currency_exchange_order_selling_payment_method-p_m_id',
            '{{%currency_exchange_order_selling_payment_method}}',
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
        $this->dropForeignKey('fk-currency_exchange_order_selling_payment_method-order_id', '{{%currency_exchange_order_selling_payment_method}}');

        $this->dropForeignKey('fk-currency_exchange_order_selling_payment_method-p_m_id', '{{%currency_exchange_order_selling_payment_method}}');

        $this->addColumn('{{%currency_exchange_order_selling_payment_method}}', 'type', $this->tinyInteger()->unsigned()->notNull());

        $this->renameTable('{{%currency_exchange_order_selling_payment_method}}', '{{%currency_exchange_order_payment_method}}');
    }
}
