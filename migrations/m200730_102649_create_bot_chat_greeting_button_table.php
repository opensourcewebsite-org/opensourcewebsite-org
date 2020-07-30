<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_greeting_button}}`.
 */
class m200730_102649_create_bot_chat_greeting_button_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_greeting_button}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string()->notNull(),
            'value' => $this->string()->notNull(),
            'updated_by' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bot_chat_greeting_button}}');
    }
}
