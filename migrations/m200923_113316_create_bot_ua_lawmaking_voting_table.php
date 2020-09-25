<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_ua_lawmaking_voting}}`.
 */
class m200923_113316_create_bot_ua_lawmaking_voting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_ua_lawmaking_voting}}', [
           'id' => $this->primaryKey()->unsigned(),
           'event_id' => $this->integer()->unsigned()->notNull(),
           'date' => $this->date()->notNull(),
           'name' => $this->text()->notNull(),
           'for' => $this->integer()->unsigned()->notNull(),
           'against' => $this->integer()->unsigned()->notNull(),
           'abstain' => $this->integer()->unsigned()->notNull(),
           'not_voting' => $this->integer()->unsigned()->notNull(),
           'total' => $this->integer()->unsigned()->notNull(),
           'presence' => $this->integer()->unsigned()->notNull(),
           'absent' => $this->integer()->unsigned()->notNull(),
       ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bot_ua_lawmaking_voting}}');
    }
}
