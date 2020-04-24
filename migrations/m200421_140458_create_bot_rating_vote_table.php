<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_rating_vote}}`.
 */
class m200421_140458_create_bot_rating_vote_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_rating_vote}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'message_id' => $this->integer()->unsigned()->notNull(),
            'provier_user_id' => $this->integer()->unsigned()->notNull(),
            'provier_voter_id' => $this->integer()->unsigned()->notNull(),
            'vote' => $this->tinyInteger()->unsigned()->notNull(),
        ]);
        $this->createIndex('idx-chat-message-voter', '{{%bot_rating_vote}}', ['chat_id','message_id','provier_voter_id'], true);
        $this->addForeignKey(
            'fk-bot_rating_vote-chat_id',
            '{{%bot_rating_vote}}',
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
        $this->dropForeignKey('fk-bot_rating_vote-chat_id', '{{%bot_rating_vote}}');
        $this->dropTable('{{%bot_rating_vote}}');
    }
}
