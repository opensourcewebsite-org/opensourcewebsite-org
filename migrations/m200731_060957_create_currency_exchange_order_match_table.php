<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency_exchange_order_match}}`.
 */
class m200731_060957_create_currency_exchange_order_match_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%currency_exchange_order_match}}', [
            'id' => $this->primaryKey()->unsigned(),
            'order_id' => $this->integer()->unsigned()->notNull(),
            'match_order_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-currency_exchange_order_match_oder_id-order_id',
            '{{%currency_exchange_order_match}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id'
        );

        $this->addForeignKey(
            'fk-currency_exchange_order_match_match_oder_id-order_id',
            '{{%currency_exchange_order_match}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-currency_exchange_order_match_match_oder_id-order_id', '{{%currency_exchange_order_match}}');

        $this->dropForeignKey('fk-currency_exchange_order_match_oder_id-order_id', '{{%currency_exchange_order_match}}');

        $this->dropTable('{{%currency_exchange_order_match}}');
    }
}
