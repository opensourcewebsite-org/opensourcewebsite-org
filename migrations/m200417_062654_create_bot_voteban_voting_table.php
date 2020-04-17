<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_voteban_voting}}`.
 */
class m200417_062654_create_bot_voteban_voting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_voteban_voting}}', [
            'id' => $this->primaryKey(),
            'provider_starter_id' => $this->integer()->unsigned()->notNull(),
            'provider_candidate_id' => $this->integer()->unsigned()->notNull(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'votingform_message_id' => $this->integer()->unsigned()->notNull(),
            'candidate_message_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->createIndex('idx-starter-candidate-chat-message', '{{%bot_voteban_voting}}', ['provider_candidate_id','chat_id','votingform_message_id','provider_starter_id'], true);
        $this->addForeignKey(
            'fk-bot_voteban_voting-chat_id',
            '{{%bot_voteban_voting}}',
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
        $this->dropForeignKey('fk-bot_voteban_voting-chat_id', '{{%bot_voteban_voting}}');
        $this->dropTable('{{%bot_voteban_voting}}');
    }
}
