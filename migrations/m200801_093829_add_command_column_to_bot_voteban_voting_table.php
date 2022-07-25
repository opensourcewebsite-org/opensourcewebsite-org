<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_voteban_voting}}`.
 */
class m200801_093829_add_command_column_to_bot_voteban_voting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_voteban_voting}}', 'command', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_voteban_voting}}', 'command');
    }
}
