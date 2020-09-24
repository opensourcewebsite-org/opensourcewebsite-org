<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%votes}}`.
 */
class m200920_055452_create_vote_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%rada_vote}}', [
            'id' => $this->primaryKey()->unsigned(),
            'id_event' => $this->integer()->unsigned()->notNull(),
            'date_event' => $this->date()->notNull(),
            'name' => $this->text()->notNull(),
            'for' => $this->integer()->unsigned()->notNull(),
            'against' => $this->integer()->unsigned()->notNull(),
            'abstain' => $this->integer()->unsigned()->notNull(),
            'not_voting' => $this->integer()->unsigned()->notNull(),
            'absent' => $this->integer()->unsigned()->notNull()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%rada_vote}}');
    }
}
