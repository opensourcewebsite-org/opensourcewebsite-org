<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payment_method}}`.
 */
class m200316_100840_create_payment_method_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('payment_method', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
            'type' => $this->tinyInteger()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('payment_method');
    }
}
