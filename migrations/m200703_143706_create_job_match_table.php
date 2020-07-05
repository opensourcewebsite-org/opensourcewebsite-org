<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_match}}`.
 */
class m200703_143706_create_job_match_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job_match}}', [
            'id' => $this->primaryKey()->unsigned(),
            'resume_id' => $this->integer()->unsigned()->notNull(),
            'vacancy_id' => $this->integer()->unsigned()->notNull(),
            'type' => $this->tinyInteger()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-job_match_resume_id-resume_id',
            '{{%job_match}}',
            'resume_id',
            '{{%resume}}',
            'id'
        );

        $this->addForeignKey(
            'fk-job_match_vacancy_id-vacancy_id',
            '{{%job_match}}',
            'vacancy_id',
            '{{%vacancy}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-job_match_resume_id-resume_id', '{{%job_match}}');

        $this->dropForeignKey('fk-job_match_vacancy_id-vacancy_id', '{{%job_match}}');

        $this->dropTable('{{%job_match}}');
    }
}
