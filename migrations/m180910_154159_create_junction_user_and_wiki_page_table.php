<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_wiki_page`.
 */
class m180910_154159_create_junction_user_and_wiki_page_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_wiki_page', [
            'user_id' => $this->integer()->unsigned(),
            'wiki_page_id' => $this->integer()->unsigned(),
            'PRIMARY KEY(user_id, wiki_page_id)',
        ]);

        $this->createIndex(
            '{{%idx-user_wiki_page-user_id}}', '{{%user_wiki_page}}', 'user_id'
        );

        $this->addForeignKey(
            '{{%fk-user_wiki_page-user_id}}', '{{%user_wiki_page}}', 'user_id', '{{%user}}', 'id', 'CASCADE'
        );

        $this->createIndex(
            '{{%idx-user_wiki_page-wiki_page_id}}', '{{%user_wiki_page}}', 'wiki_page_id'
        );

        $this->addForeignKey(
            '{{%fk-user_wiki_page-wiki_page_id}}', '{{%user_wiki_page}}', 'wiki_page_id', '{{%wiki_page}}', 'id', 'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user_wiki_page');
    }
}
