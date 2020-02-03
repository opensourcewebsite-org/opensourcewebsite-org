<?php

use yii\db\Migration;

/**
 * Class m200131_103926_creates_groupchats_table
 */
class m200131_103926_creates_groupchats_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_chats}}', [
            '_id' => $this->primaryKey(),
            'owner_id' => $this->integer()->notNull(),
            'tg_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'mode' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-chats-owner-id-to-users',
            'group_chats',
            'owner_id',
            'groupusers',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%group_chats}}');

        $this->dropForeignKey('fk-chats-owner-id-to-users', 'group_chats');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200131_103926_creates_groupchats_table cannot be reverted.\n";

        return false;
    }
    */
}
