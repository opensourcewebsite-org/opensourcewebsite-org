<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Handles the creation of table `{{%bot_chat_bot_user}}`.
 */
class m200213_191549_create_bot_chat_bot_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_bot_user}}', [
            'id' => $this->primaryKey(),
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

        $rows = (new Query())
            ->select([
                'bot_chat.id as chat_id',
                'bot_user.id as user_id',
            ])
            ->from('bot_chat')
            ->join('INNER JOIN', 'bot_user', 'bot_user.provider_user_id = bot_chat.chat_id')
            ->all();

        foreach ($rows as $row) {
            $this->insert(
                '{{%bot_chat_bot_user}}',
                [
                    'chat_id' => $row['chat_id'],
                    'user_id' => $row['user_id'],
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
}
