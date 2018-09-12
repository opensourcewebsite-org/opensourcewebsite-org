<?php

use yii\db\Migration;

/**
 * Handles the creation of table `wiki_page`.
 */
class m180910_154053_create_wiki_page_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wiki_page}}', [
            'id' => $this->primaryKey()->unsigned(),
            'language_id' => $this->integer()->unsigned()->notNull(),
            'ns' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string()->notNull(),
            'group_id' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned()
        ]);

        $this->createIndex('{{%idx-wiki_page-title}}', '{{%wiki_page}}', 'title');

        $this->createIndex('{{%idx-wiki_page-language_id}}', '{{%wiki_page}}', 'language_id');

        $this->addForeignKey('{{%fk-wiki_page-language}}', '{{%wiki_page}}', 'language_id', '{{%wiki_language}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wiki_page}}');
    }
}
