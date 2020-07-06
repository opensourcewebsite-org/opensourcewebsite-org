<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_vacancy_keyword}}`.
 */
class m200704_020139_create_job_vacancy_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('job_vacancy_keyword', [
            'id' => $this->primaryKey()->unsigned(),
            'vacancy_id' => $this->integer()->unsigned()->notNull(),
            'job_keyword_id' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('job_vacancy_keyword');
    }
}
