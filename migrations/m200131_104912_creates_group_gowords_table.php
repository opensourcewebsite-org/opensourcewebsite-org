<?php

use yii\db\Migration;

/**
 * Class m200131_104912_creates_group_gowords_table
 */
class m200131_104912_creates_group_gowords_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('group_gowords', [
            '_id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->notNull(),
            'text' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-gowords-chat_id-to-chats',
            'group_gowords',
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
        $this->dropTable('group_gowords');

        $this->dropForeignKey('fk-gowords-chat_id-to-chats', 'group_gowords');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200131_104912_creates_group_gowords_table cannot be reverted.\n";

        return false;
    }
    */
}
