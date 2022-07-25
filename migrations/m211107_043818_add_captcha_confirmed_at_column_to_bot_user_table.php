<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_user}}`.
 */
class m211107_043818_add_captcha_confirmed_at_column_to_bot_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_user}}', 'captcha_confirmed_at', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_user}}', 'captcha_confirmed_at');
    }
}
