<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency_exchange_order_response}}`.
 */
class m210609_101839_create_currency_exchange_order_response_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%currency_exchange_order_response}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'order_id' => $this->integer()->unsigned()->notNull(),
            'viewed_at' => $this->integer()->unsigned(),
            'archived_at' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%currency_exchange_order_response}}');
    }
}
