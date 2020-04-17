<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_voteban_vote}}`.
 */
class m200417_062702_create_bot_voteban_vote_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_voteban_vote}}', [
            'id' => $this->primaryKey(),
            'provider_voter_id' => $this->integer()->unsigned()->notNull(),
            'provider_candidate_id' => $this->integer()->unsigned()->notNull(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'vote' => $this->tinyInteger()->notNull(),
        ]);

        $this->createIndex('idx-voter-candidate-chat', '{{%bot_voteban_vote}}', ['provider_voter_id','provider_candidate_id','chat_id'], true);
        $this->addForeignKey(
            'fk-bot_voteban_vote-chat_id',
            '{{%bot_voteban_vote}}',
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
        $this->dropForeignKey('fk-bot_voteban_vote-chat_id', '{{%bot_voteban_vote}}');
        $this->dropTable('{{%bot_voteban_vote}}');
    }
}
