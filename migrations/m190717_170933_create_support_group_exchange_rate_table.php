<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%support_group_exchange_rate}}`.
 */
class m190717_170933_create_support_group_exchange_rate_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%support_group_exchange_rate}}', [
            'id' => $this->primaryKey()->unsigned(),
            'support_group_id' => $this->integer()->unsigned()->notNull(),
            'code' => $this->string()->notNull(),
            'name' => $this->string(),
            'buying_rate' => $this->decimal(15, 8),
            'selling_rate' => $this->decimal(15, 8),
            'is_default' => $this->boolean()->defaultValue(0)->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'created_by' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned(),
            'updated_by' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%support_group_exchange_rate}}');
    }
}
