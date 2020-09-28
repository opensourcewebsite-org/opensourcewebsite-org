<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_ua_lawmaking_voting}}`.
 */
class m200926_121833_add_sent_at_column_to_bot_ua_lawmaking_voting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_ua_lawmaking_voting}}', 'message_id', $this->integer()->unsigned());
        $this->addColumn('{{%bot_ua_lawmaking_voting}}', 'sent_at', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_ua_lawmaking_voting}}', 'message_id');
        $this->dropColumn('{{%bot_ua_lawmaking_voting}}', 'sent_at');
    }
}
