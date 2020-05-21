<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_command_alias}}`.
 */
class m200521_051648_create_bot_command_alias_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_command_alias}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'command' => $this->string()->notNull(),
            'text' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_command_alias-chat_id',
            '{{%bot_command_alias}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_command_alias-group_id',
            '{{%bot_command_alias}}'
        );

        $this->dropTable('{{%bot_command_alias}}');
    }
}
