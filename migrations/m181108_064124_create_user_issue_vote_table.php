<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_issue_vote`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `issue`
 */
class m181108_064124_create_user_issue_vote_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_issue_vote', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'issue_id' => $this->integer()->unsigned()->notNull(),
            'vote_type' => $this->tinyInteger()->unsigned()->notNull(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-user_issue_vote-user_id',
            'user_issue_vote',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-user_issue_vote-user_id',
            'user_issue_vote',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `issue_id`
        $this->createIndex(
            'idx-user_issue_vote-issue_id',
            'user_issue_vote',
            'issue_id'
        );

        // add foreign key for table `issue`
        $this->addForeignKey(
            'fk-user_issue_vote-issue_id',
            'user_issue_vote',
            'issue_id',
            'issue',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-user_issue_vote-user_id',
            'user_issue_vote'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-user_issue_vote-user_id',
            'user_issue_vote'
        );

        // drops foreign key for table `issue`
        $this->dropForeignKey(
            'fk-user_issue_vote-issue_id',
            'user_issue_vote'
        );

        // drops index for column `issue_id`
        $this->dropIndex(
            'idx-user_issue_vote-issue_id',
            'user_issue_vote'
        );

        $this->dropTable('user_issue_vote');
    }
}
