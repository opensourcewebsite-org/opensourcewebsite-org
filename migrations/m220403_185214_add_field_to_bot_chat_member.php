<?php

use yii\db\Migration;

/**
 * Class m220403_185214_add_field_to_bot_chat_member
 */
class m220403_185214_add_field_to_bot_chat_member extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //add column
        $this->addColumn('{{%bot_chat_member}}', 'invite_user_id', $this->integer()->unsigned()->after('user_id'));

        // creates index for column `user_id`
        $this->createIndex(
            'idx-bot_chat_member-invite_user_id',
            '{{%bot_chat_member}}',
            'invite_user_id'
        );

        //create foreign key
        $this->addForeignKey(
            'fk-bot_chat_member-invite_user_id',
            '{{%bot_chat_member}}',
            'invite_user_id',
            '{{%bot_user}}',
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
        //drop FK
        $this->dropForeignKey(
            'fk-bot_chat_member-invite_user_id',
            '{{%bot_chat_member}}'
        );

        //drop index
        $this->dropIndex('idx-bot_chat_member-invite_user_id', '{{%bot_chat_member}}');

        //drop column
        $this->dropColumn('{{%bot_chat_member}}', 'invite_user_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220403_185214_add_field_to_bot_chat_member cannot be reverted.\n";

        return false;
    }
    */
}
