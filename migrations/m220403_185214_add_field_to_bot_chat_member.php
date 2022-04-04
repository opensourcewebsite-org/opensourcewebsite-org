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
        $this->addColumn("{{%bot_chat_member}}", "invite_user_id", $this->integer()->null()->after("user_id"));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("{{%bot_chat_member}}", "invite_user_id");
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
