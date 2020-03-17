<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payment_method_currency}}`.
 */
class m200316_102033_create_payment_method_currency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('payment_method_currency', [
            'id' => $this->primaryKey()->unsigned(),
            'payment_method_id' => $this->integer()->unsigned()->notNull(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('payment_method_currency');
    }
}
