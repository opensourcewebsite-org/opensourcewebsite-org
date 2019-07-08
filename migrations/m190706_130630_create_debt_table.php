<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%debt}}`.
 */
class m190706_130630_create_debt_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%debt}}', [
            'id' => $this->primaryKey()->unsigned(),
            'from_user_id' => $this->integer()->unsigned()->notNull(),
            'to_user_id' => $this->integer()->unsigned()->notNull(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'amount' => $this->integer()->unsigned()->notNull(),
            'status' => $this->tinyInteger()->unsigned()->notNull(),
            'valid_from_date' => $this->date(),
            'valid_from_time' => $this->time(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->unsigned(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%debt}}');
    }
}
