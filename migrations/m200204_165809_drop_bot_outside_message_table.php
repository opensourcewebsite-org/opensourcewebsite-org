<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%bot_outside_message}}`.
 */
class m200204_165809_drop_bot_outside_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%bot_outside_message}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%bot_outside_message}}', [
            'id' => $this->primaryKey(),
            'bot_id' => $this->integer()->unsigned()->notNull(),
            'bot_client_id' => $this->integer()->unsigned(),
            'provider_message_id' => $this->integer()->unsigned()->notNull(),
            'provider_chat_id' => $this->bigInteger()->unsigned(),
            'message' => $this->text()->notNull(),
            'type' => $this->tinyInteger()
                ->unsigned()
                ->notNull()
                ->defaultValue(0),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            '{{%fk-bot_outside_message-bot}}',
            '{{%bot_outside_message}}',
            'bot_id',
            '{{%bot}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            '{{%fk-bot_outside_message-bot_client}}',
            '{{%bot_outside_message}}',
            'bot_client_id',
            '{{%bot_client}}',
            'id',
            'CASCADE'
        );
    }
}
