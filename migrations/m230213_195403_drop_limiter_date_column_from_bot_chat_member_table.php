<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%bot_chat_member}}`.
 */
class m230213_195403_drop_limiter_date_column_from_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%bot_chat_member}}', 'limiter_date');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%bot_chat_member}}', 'limiter_date', $this->date());
    }
}
