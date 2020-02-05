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
        $this->createTable('{{%group_stopword}}', [
            '_id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'text' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-stopword-chat_id-to-chat',
            '{{%group_stopword}}',
            'chat_id',
            '{{%group_chat}}',
            '_id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropForeignKey('fk-stopword-chat_id-to-chat', '{{%group_stopword}}');

        $this->dropTable('{{%group_stopword}}');
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
