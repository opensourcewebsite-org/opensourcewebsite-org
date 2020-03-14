<?php

use yii\db\Migration;

/**
 * Class m200308_141651_alter_column_telegram_user_id_in_bot_chat_member_table
 */
class m200308_141651_alter_column_telegram_user_id_in_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%bot_chat_member}}', 'telegram_user_id', 'user_id');

        $this->addForeignKey(
            'fk-bot_chat_member-user_id',
            '{{%bot_chat_member}}',
            'user_id',
            '{{%bot_user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bot_chat_member-user_id');

        $this->renameColumn('{{%bot_chat_member}}', 'user_id', 'telegram_user_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200308_141651_alter_column_telegram_user_id_in_bot_chat_member_table cannot be reverted.\n";

        return false;
    }
    */
}
