<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency_exhange_order_payment_method}}`.
 */
class m200317_092159_create_currency_exhange_order_payment_method_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('currency_exhange_order_payment_method', [
            'id' => $this->primaryKey()->unsigned(),
            'order_id' => $this->integer()->unsigned()->notNull(),
            'payment_method_id' => $this->integer()->unsigned()->notNull(),
            'type' => $this->tinyInteger()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('currency_exhange_order_payment_method');
    }
}
