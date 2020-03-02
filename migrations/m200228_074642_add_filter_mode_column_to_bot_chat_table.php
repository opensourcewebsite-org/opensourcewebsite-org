<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat}}`.
 */
class m200228_074642_add_filter_mode_column_to_bot_chat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat}}', 'filter_mode', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat}}', 'filter_mode');
    }
}
