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
        $this->createTable('{{%group_chat}}', [
            '_id' => $this->primaryKey()->unsigned(),
            'owner_id' => $this->integer()->unsigned()->notNull(),
            'tg_id' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'mode' => $this->tinyInteger()->notNull(),
            'enabled' => $this->boolean()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-chat-owner-id-to-user',
            '{{%group_chat}}',
            'owner_id',
            '{{%group_user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-chat-owner-id-to-user', '{{%group_chat}}');

        $this->dropTable('{{%group_chat}}');
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
