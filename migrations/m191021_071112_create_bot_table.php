<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot}}`.
 */
class m191021_071112_create_bot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'token' => $this->string(255)->notNull(),
            'status' => $this->smallInteger(1)->unsigned()->defaultValue(0)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bot}}');
    }
}
