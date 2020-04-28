<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_rating_voting}}`.
 */
class m200421_140446_create_bot_rating_voting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_rating_voting}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'provider_starter_id' => $this->integer()->unsigned()->notNull(),
            'candidate_message_id' => $this->integer()->unsigned()->notNull(),
            'voting_message_id' => $this->integer()->unsigned()->notNull(),
        ]);
        $this->createIndex('idx-chat-message-voting_message', '{{%bot_rating_voting}}', ['chat_id','candidate_message_id','voting_message_id'], true);
        $this->addForeignKey(
            'fk-bot_rating_voting-chat_id',
            '{{%bot_rating_voting}}',
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
        $this->dropForeignKey('fk-bot_rating_voting-chat_id', '{{%bot_rating_voting}}');
        $this->dropTable('{{%bot_rating_voting}}');
    }
}
