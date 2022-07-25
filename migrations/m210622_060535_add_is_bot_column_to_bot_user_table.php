<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_user}}`.
 */
class m210622_060535_add_is_bot_column_to_bot_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_user}}', 'is_bot', $this->tinyInteger()->notNull()->unsigned()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_user}}', 'is_bot');
    }
}
