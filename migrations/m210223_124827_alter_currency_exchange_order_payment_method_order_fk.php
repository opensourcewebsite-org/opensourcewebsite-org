<?php

use yii\db\Migration;

/**
 * Class m210223_124827_alter_currency_exchange_order_payment_method_order_fk
 */
class m210223_124827_alter_currency_exchange_order_payment_method_order_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_order_ex_payment_method_order_id', '{{%currency_exchange_order_payment_method}}');

        $this->addForeignKey(
            'fk_order_ex_payment_method_order_id',
            '{{%currency_exchange_order_payment_method}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_order_ex_payment_method_order_id', '{{%currency_exchange_order_payment_method}}');
        $this->addForeignKey(
            'fk_order_ex_payment_method_order_id',
            '{{%currency_exchange_order_payment_method}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210223_124827_alter_currency_exchange_order_payment_method_order_fk cannot be reverted.\n";

        return false;
    }
    */
}
