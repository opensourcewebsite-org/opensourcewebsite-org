<?php

use yii\db\Migration;

/**
 * Class m180908_042200_user_wiki_token_table
 */
class m180908_042200_user_wiki_token_table extends Migration
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_wiki_token}}');
    }
}
