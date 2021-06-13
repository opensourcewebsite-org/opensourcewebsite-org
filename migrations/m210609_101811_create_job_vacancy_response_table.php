<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_vacancy_response}}`.
 */
class m210609_101811_create_job_vacancy_response_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job_vacancy_response}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'vacancy_id' => $this->integer()->unsigned()->notNull(),
            'viewed_at' => $this->integer()->unsigned(),
            'archived_at' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%job_vacancy_response}}');
    }
}
