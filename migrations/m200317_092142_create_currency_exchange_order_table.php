<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency_exchange_order}}`.
 */
class m200317_092142_create_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('currency_exchange_order', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'selling_currency_id' => $this->integer()->unsigned()->notNull(),
            'buying_currency_id' => $this->integer()->unsigned()->notNull(),
            'selling_rate' => $this->decimal(15, 8)->defaultValue(null),
            'buying_rate' => $this->decimal(15, 8)->defaultValue(null),
            'selling_currency_min_amount' => $this->decimal(18, 8)->defaultValue(null),
            'selling_currency_max_amount' => $this->decimal(18, 8)->defaultValue(null),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'renewed_at' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('currency_exchange_order');
    }
}
