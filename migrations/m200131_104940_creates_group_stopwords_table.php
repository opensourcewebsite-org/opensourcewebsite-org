<?php

use yii\db\Migration;

/**
 * Class m200131_104940_creates_group_stopwords_table
 */
class m200131_104940_creates_group_stopwords_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('group_stopwords', [
            '_id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->notNull(),
            'text' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-stopwords-chat_id-to-chats',
            'group_stopwords',
            'chat_id',
            'group_chats',
            '_id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('group_stopwords');

        $this->dropForeignKey('fk-stopwords-chat_id-to-chats', 'group_stopwords');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200131_104940_creates_group_stopwords_table cannot be reverted.\n";

        return false;
    }
    */
}
