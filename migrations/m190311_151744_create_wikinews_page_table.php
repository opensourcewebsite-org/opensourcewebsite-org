<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wikinews_page}}`.
 */
class m190311_151744_create_wikinews_page_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wikinews_page}}', [
            'id' => $this->primaryKey()->unsigned(),
            'language_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string()->notNull(),
            'group_id' => $this->integer()->unsigned(),
            'pageid' => $this->integer()->unsigned(),
            'created_by' => $this->integer()->unsigned(),
            'created_at' => $this->integer()->unsigned(),
            'parsed_at' => $this->integer()->unsigned(),
        ]);

        $this->createIndex('{{%idx-wikinews_page-title}}', '{{%wikinews_page}}', 'title');
        $this->createIndex('{{%idx-wikinews_page-language_id}}', '{{%wikinews_page}}', 'language_id');
        $this->createIndex('{{%idx-wikinews_page-created_by}}', '{{%wikinews_page}}', 'created_by');

        $this->addForeignKey('{{%fk-wikinews_page-language}}', '{{%wikinews_page}}', 'language_id', '{{%wikinews_language}}', 'id', 'CASCADE');
        $this->addForeignKey('{{%fk-wikinews_page-created_by}}', '{{%wikinews_page}}', 'created_by', '{{%user}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wikinews_page}}');
    }
}
