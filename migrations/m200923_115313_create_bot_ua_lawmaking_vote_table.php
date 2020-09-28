<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_ua_lawmaking_vote}}`.
 */
class m200923_115313_create_bot_ua_lawmaking_vote_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_ua_lawmaking_vote}}', [
            'id' => $this->primaryKey()->unsigned(),
            'message_id' => $this->integer()->unsigned()->notNull(),
            'provider_voter_id' => $this->integer()->unsigned()->notNull(),
            'vote' => $this->tinyInteger()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bot_ua_lawmaking_vote}}');
    }
}
