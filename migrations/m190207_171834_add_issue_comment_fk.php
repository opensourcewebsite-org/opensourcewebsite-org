<?php

use yii\db\Migration;

/**
 * Class m190207_171834_add_issue_comment_fk
 */
class m190207_171834_add_issue_comment_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-issue_comment-issue_id',
            'issue_comment',
            'issue_id',
            'issue',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-issue_comment-user_id',
            'issue_comment',
            'user_id',
            'user',
            'id'
        );


        $this->createIndex(
            'idx-issue_comment-parent_id',
            'issue_comment',
            'parent_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-issue_comment-issue_id', 'issue_comment');
        $this->dropForeignKey('fk-issue_comment-user_id', 'issue_comment');
        $this->dropIndex('idx-issue_comment-parent_id', 'issue_comment');
    }
}
