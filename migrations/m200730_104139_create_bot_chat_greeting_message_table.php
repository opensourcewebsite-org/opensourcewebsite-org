<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_greeting_message}}`.
 */
class m200730_104139_create_bot_chat_greeting_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_greeting_message}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'value' => $this->text()->notNull(),
            'updated_by' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bot_chat_greeting_message}}');
    }
}
