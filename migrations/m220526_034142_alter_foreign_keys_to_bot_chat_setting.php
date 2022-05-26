<?php

use yii\db\Migration;

/**
 * Class m220526_034142_alter_foreign_keys_to_bot_chat_setting
 */
class m220526_034142_alter_foreign_keys_to_bot_chat_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-bot_setting-chat_id', '{{%bot_chat_setting}}');
        $this->dropForeignKey('fk-bot_chat_setting-updated_by', '{{%bot_chat_setting}}');

        $this->addForeignKey(
            'fk-bot_chat_setting-chat_id',
            '{{%bot_chat_setting}}',
            'chat_id',
            '{{%bot_chat}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_setting-updated_by',
            '{{%bot_chat_setting}}',
            'updated_by',
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
        $this->dropForeignKey('fk-bot_chat_setting-chat_id', '{{%bot_chat_setting}}');
        $this->dropForeignKey('fk-bot_chat_setting-updated_by', '{{%bot_chat_setting}}');

        $this->addForeignKey(
            'fk-bot_setting-chat_id',
            '{{%bot_chat_setting}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_setting-updated_by',
            '{{%bot_chat_setting}}',
            'updated_by',
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
        echo "m220526_034142_alter_foreign_keys_to_bot_chat_setting cannot be reverted.\n";

        return false;
    }
    */
}
