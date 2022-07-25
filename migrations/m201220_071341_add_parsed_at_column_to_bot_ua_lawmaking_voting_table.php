<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_ua_lawmaking_voting}}`.
 */
class m201220_071341_add_parsed_at_column_to_bot_ua_lawmaking_voting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_ua_lawmaking_voting}}', 'parsed_at', $this->integer()->unsigned()->after('absent'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_ua_lawmaking_voting}}', 'parsed_at');
    }
}
