<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%issue_comment}}`.
 */
class m190207_171806_create_issue_comment_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('issue_comment', [
            'id' => $this->primaryKey()->unsigned(),
            'parent_id' => $this->integer()->unsigned(),
            'message' => $this->text()->notNull(),
            'issue_id' => $this->integer()->notNull()->unsigned(),
            'user_id' => $this->integer()->notNull()->unsigned(),
            'created_at' => $this->integer()->notNull()->unsigned(),
            'updated_at' => $this->integer()->notNull()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('issue_comment');
    }
}
