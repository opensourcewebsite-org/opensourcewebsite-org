<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%timezone}}`.
 */
class m200311_202234_create_timezone_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%timezone}}', [
            'code' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string()->notNull(),
            'offset' => $this->integer()->notNull(),
            'PRIMARY KEY (code)',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%timezone}}');
    }
}
