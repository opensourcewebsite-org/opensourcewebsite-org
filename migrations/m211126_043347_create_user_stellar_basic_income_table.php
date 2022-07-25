<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_stellar_basic_income}}`.
 */
class m211126_043347_create_user_stellar_basic_income_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_stellar_basic_income}}', [
            'id' => $this->primaryKey()->unsigned(),
            'account_id' => $this->string()->notNull(),
            'income' => $this->decimal(18, 8)->unsigned()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'processed_at' => $this->integer()->unsigned(),
            'result_code' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_stellar_basic_income}}');
    }
}
