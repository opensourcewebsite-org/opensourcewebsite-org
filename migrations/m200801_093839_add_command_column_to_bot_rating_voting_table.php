<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_rating_voting}}`.
 */
class m200801_093839_add_command_column_to_bot_rating_voting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_rating_voting}}', 'command', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_rating_voting}}', 'command');
    }
}
