<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_resume_keyword}}`.
 */
class m200704_020147_create_job_resume_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('job_resume_keyword', [
            'id' => $this->primaryKey()->unsigned(),
            'resume_id' => $this->integer()->unsigned()->notNull(),
            'job_keyword_id' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('job_resume_keyword');
    }
}
