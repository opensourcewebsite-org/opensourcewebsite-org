<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Handles the creation of table `{{%bot_chat}}`.
 */
class m200213_003152_create_bot_chat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->bigInteger()->notNull()->unique(),
            'type' => $this->string()->notNull(),
            'title' => $this->string(),
            'username' => $this->string(),
            'first_name' => $this->string(),
            'last_name' => $this->string(),
            'bot_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat-bot_id',
            '{{%bot_chat}}',
            'bot_id',
            '{{%bot}}',
            'id'
        );

        $rows = (new Query())
            ->select([
                'bot_id',
                'provider_user_id',
                'provider_user_first_name',
                'provider_user_last_name',
                'provider_user_name',
            ])
            ->from('bot_user')
            ->all();

        foreach ($rows as $row) {
            $this->insert('{{%bot_chat}}',
                [
                    'bot_id' => $row['bot_id'],
                    'chat_id' => $row['provider_user_id'],
                    'username' => $row['provider_user_name'],
                    'first_name' => $row['provider_user_first_name'],
                    'last_name' => $row['provider_user_last_name'],
                    'type' => 'private',
                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );
        }

        $this->dropForeignKey(
            'fk-bot_client-bot_id',
            'bot_user'
        );

        $this->dropColumn('{{%bot_user}}', 'bot_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%bot_user}}', 'bot_id', $this->integer()->unsigned());

        $this->addForeignKey(
            'fk-bot_client-bot_id',
            'bot_user',
            'bot_id',
            'bot',
            'id',
            'CASCADE'
        );

        $rows = (new Query())->select(['bot_id', 'chat_id'])->from('bot_chat')->all();
        foreach ($rows as $row) {
            $this->update(
                '{{%bot_user}}',
                ['bot_id' => $row['bot_id']],
                ['provider_user_id' => $row['chat_id']]
            );
        }

        $this->dropForeignKey(
            'fk-bot_chat-bot_id',
            '{{%bot_chat}}',
        );

        $this->dropTable('{{%bot_chat}}');
    }
}
