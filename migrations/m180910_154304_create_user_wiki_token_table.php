<?php

use yii\db\Migration;

/**
 * Class m180910_154304_create_user_wiki_token_table
 */
class m180910_154304_create_user_wiki_token_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_wiki_token}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'language_id' => $this->integer()->unsigned()->notNull(),
            'token' => $this->string()->notNull(),
            'wiki_username' => $this->string()->notNull(),
            'status' => $this->boolean()->notNull()->defaultValue(0),
            'updated_at' => $this->integer()->unsigned(),
        ]);

        $this->createIndex(
            '{{%idx-user_wiki_token-user_id}}', '{{%user_wiki_token}}', 'user_id'
        );

        $this->addForeignKey(
            '{{%fk-user_wiki_token-user_id}}', '{{%user_wiki_token}}', 'user_id', '{{%user}}', 'id', 'CASCADE'
        );

        $this->createIndex(
            '{{%idx-user_wiki_token-language_id}}', '{{%user_wiki_token}}', 'language_id'
        );

        $this->addForeignKey(
            '{{%fk-user_wiki_token-language_id}}', '{{%user_wiki_token}}', 'language_id', '{{%wiki_language}}', 'id', 'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_wiki_token}}');
    }
}
