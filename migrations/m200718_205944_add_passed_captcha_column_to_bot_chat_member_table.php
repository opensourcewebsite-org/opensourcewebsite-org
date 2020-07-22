<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat_member}}`.
 */
class m200718_205944_add_passed_captcha_column_to_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat_member}}', 'role', $this->tinyInteger()->notNull()->defaultValue(0)->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat_member}}', 'role');
    }
}
