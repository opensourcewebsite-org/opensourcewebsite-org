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
            'chat_id' => $this->bigInteger()->notNull(),
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
                'provider_user_id',
                'provider_user_first_name',
                'provider_user_last_name',
                'provider_user_name',
            ])
            ->from('bot_user')
            ->all();

        $botId = (new Query())->select('id')->from('bot')->one();

        foreach ($rows as $row) {
            $this->insert('{{%bot_chat}}',
                [
                    'bot_id' => $botId,
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_chat-bot_id',
            '{{%bot_chat}}'
        );

        $this->dropTable('{{%bot_chat}}');
    }
}
