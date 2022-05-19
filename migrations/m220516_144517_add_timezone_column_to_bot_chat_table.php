<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat}}`.
 */
class m220516_144517_add_timezone_column_to_bot_chat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat}}', 'timezone', $this->smallInteger()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat}}', 'timezone');
    }
}
