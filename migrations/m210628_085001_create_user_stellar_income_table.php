<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_stellar_income}}`.
 */
class m210628_085001_create_user_stellar_income_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_stellar_income}}', [
            'id' => $this->primaryKey()->unsigned(),
            'account_id' => $this->string()->notNull(),
            'asset_code' => $this->string()->notNull(),
            'balance' => $this->string()->notNull(),
            'income' => $this->string()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'processed_at' => $this->integer()->unsigned()->notNull(),
            'result_code' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_stellar_income}}');
    }
}
