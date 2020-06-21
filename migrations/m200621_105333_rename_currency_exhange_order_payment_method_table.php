<?php

use yii\db\Migration;

/**
 * Class m200621_105333_rename_currency_exhange_order_payment_method_table
 */
class m200621_105333_rename_currency_exhange_order_payment_method_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('currency_exhange_order_payment_method', 'currency_exchange_order_payment_method');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('currency_exchange_order_payment_method', 'currency_exhange_order_payment_method');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200621_105333_rename_currency_exhange_order_payment_method_table cannot be reverted.\n";

        return false;
    }
    */
}
