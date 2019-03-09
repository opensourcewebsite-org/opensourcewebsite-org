<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wiki_news}}`.
 */
class m190309_095426_create_wiki_news_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wiki_news}}', [
            'id' => $this->primaryKey()->unsigned(),
            'title' => $this->text()->notNull(),
            'link' => $this->string(2083)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wiki_news}}');
    }
}
