<?php

use yii\db\Migration;

/**
 * Class m220821_115518_create_bot_chat_member_phrase_table
 */
class m220821_115518_create_bot_chat_member_phrase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_member_phrase}}', [
            'id' => $this->primaryKey()->unsigned(),
            'member_id' => $this->integer()->unsigned()->notNull(),
            'phrase_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_member_phrase-member_id',
            '{{%bot_chat_member_phrase}}',
            'member_id',
            '{{%bot_chat_member}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_member_phrase-phrase_id',
            '{{%bot_chat_member_phrase}}',
            'phrase_id',
            '{{%bot_chat_phrase}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bot_chat_member_phrase-member_id', '{{%bot_chat_member_phrase}}');
        $this->dropForeignKey('fk-bot_chat_member_phrase-phrase_id', '{{%bot_chat_member_phrase}}');

        $this->dropTable('{{%bot_chat_member_phrase}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220821_115518_create_bot_chat_member_phrase_table cannot be reverted.\n";

        return false;
    }
    */
}
