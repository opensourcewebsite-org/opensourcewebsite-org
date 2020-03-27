<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%bot_chat_bot_user}}`.
 */
class m200315_102123_drop_bot_chat_bot_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-bot_chat_bot_user-chat_id',
            '{{%bot_chat_bot_user}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_bot_user-user_id',
            '{{%bot_chat_bot_user}}'
        );

        $this->dropTable('{{%bot_chat_bot_user}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%bot_chat_bot_user}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_bot_user-chat_id',
            '{{%bot_chat_bot_user}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_bot_user-user_id',
            '{{%bot_chat_bot_user}}',
            'user_id',
            '{{%bot_user}}',
            'id'
        );
    }
}
