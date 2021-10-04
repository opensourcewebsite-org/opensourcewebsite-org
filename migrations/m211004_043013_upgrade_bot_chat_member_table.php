<?php

use yii\db\Migration;
use app\modules\bot\models\ChatMember;

/**
 * Class m211004_043013_upgrade_bot_chat_member_table
 */
class m211004_043013_upgrade_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            '{{%bot_chat_member}}',
            ['role' => ChatMember::ROLE_ADMINISTRATOR],
            [
                'or',
                ['status' => ChatMember::STATUS_CREATOR],
                ['status' => ChatMember::STATUS_ADMINISTRATOR],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211004_043013_upgrade_bot_chat_member_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211004_043013_upgrade_bot_chat_member_table cannot be reverted.\n";

        return false;
    }
    */
}
