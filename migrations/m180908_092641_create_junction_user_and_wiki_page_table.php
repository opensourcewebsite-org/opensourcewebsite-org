<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_wiki_page`.
 */
class m180908_092641_create_junction_user_and_wiki_page_table extends Migration
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user_wiki_page');
    }
}
