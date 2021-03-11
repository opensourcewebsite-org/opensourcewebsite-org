<?php

use yii\db\Migration;

/**
 * Class m210222_205855_create_currency_exchange_order_payment_method
 */
class m210222_205855_create_currency_exchange_order_payment_method extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%currency_exchange_order_payment_method}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->unsigned()->notNull(),
            'payment_method_id' => $this->integer()->unsigned()->notNull(),
            'type' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->createIndex(
            'idx_cur_exchange_payment_method_order_id',
            '{{%currency_exchange_order_payment_method}}',
            ['id', 'order_id']
        );

        $this->addForeignKey(
            'fk_order_ex_payment_method_order_id',
            '{{%currency_exchange_order_payment_method}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id'
        );

        $this->createIndex(
            'idx_cur_exchange_payment_method_payment_method_id',
            '{{%currency_exchange_order_payment_method}}',
            ['id', 'payment_method_id']
        );

        $this->addForeignKey(
            'fk_cur_exchange_order_payment_method_payment_method_id',
            '{{%currency_exchange_order_payment_method}}',
            'payment_method_id',
            '{{%payment_method}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_cur_exchange_order_payment_method_payment_method_id','{{%currency_exchange_order_payment_method}}');
        $this->dropIndex('idx_cur_exchange_payment_method_payment_method_id','{{%currency_exchange_order_payment_method}}');
        $this->dropForeignKey('fk_order_ex_payment_method_order_id','{{%currency_exchange_order_payment_method}}');
        $this->dropIndex('idx_cur_exchange_payment_method_order_id','{{%currency_exchange_order_payment_method}}');
        $this->dropTable('{{%currency_exchange_order_payment_method}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210222_205855_create_currency_exchange_order_payment_method cannot be reverted.\n";

        return false;
    }
    */
}
