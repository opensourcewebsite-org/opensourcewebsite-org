<?php

use yii\db\Migration;

/**
 * Class m220526_034132_alter_foreign_keys_to_bot_chat_member
 */
class m220526_034132_alter_foreign_keys_to_bot_chat_member extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-bot_admin-chat_id', '{{%bot_chat_member}}');
        $this->dropForeignKey('fk-bot_chat_member-user_id', '{{%bot_chat_member}}');

        $this->addForeignKey(
            'fk-bot_chat_member-chat_id',
            '{{%bot_chat_member}}',
            'chat_id',
            '{{%bot_chat}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_member-user_id',
            '{{%bot_chat_member}}',
            'user_id',
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
        $this->dropForeignKey('fk-bot_chat_member-chat_id', '{{%bot_chat_member}}');
        $this->dropForeignKey('fk-bot_chat_member-user_id', '{{%bot_chat_member}}');

        $this->addForeignKey(
            'fk-bot_admin-chat_id',
            '{{%bot_chat_member}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_member-user_id',
            '{{%bot_chat_member}}',
            'user_id',
            '{{%bot_user}}',
            'id'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220526_034132_alter_foreign_keys_to_bot_chat_member cannot be reverted.\n";

        return false;
    }
    */
}
