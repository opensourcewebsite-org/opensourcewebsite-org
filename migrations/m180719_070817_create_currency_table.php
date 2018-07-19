<?php

use yii\db\Migration;

/**
 * Handles the creation of table `currency`.
 */
class m180719_070817_create_currency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('currency', [
            'id' => $this->primaryKey()->unsigned(),
            'code' => $this->string()->notNull()->unique(),
            'name' => $this->string()->notNull(),
            'symbol' => $this->string(4),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('currency');
    }
}
