<?php

use yii\db\Migration;

/**
 * Handles the creation of table `issue`.
 * Has foreign keys to the tables:
 *
 * - `user`
 */
class m181108_064111_create_issue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('issue', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->notNull(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-issue-user_id',
            'issue',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-issue-user_id',
            'issue',
            'user_id',
            'user',
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
            'fk-issue-user_id',
            'issue'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-issue-user_id',
            'issue'
        );

        $this->dropTable('issue');
    }
}
