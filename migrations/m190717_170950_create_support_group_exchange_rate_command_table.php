<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%support_group_exchange_rate_command}}`.
 */
class m190717_170950_create_support_group_exchange_rate_command_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%support_group_exchange_rate_command}}', [
            'id' => $this->primaryKey()->unsigned(),
            'command' => $this->string()->notNull(),
            'type' => $this->tinyInteger()->unsigned()->notNull(),
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
        $this->dropTable('{{%support_group_exchange_rate_command}}');
    }
}
