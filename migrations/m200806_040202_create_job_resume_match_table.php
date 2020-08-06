<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_resume_match}}`.
 */
class m200806_040202_create_job_resume_match_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-job_match_resume_id-resume_id', '{{%job_match}}');

        $this->dropForeignKey('fk-job_match_vacancy_id-vacancy_id', '{{%job_match}}');

        $this->dropTable('{{%job_match}}');

        $this->createTable('{{%job_resume_match}}', [
            'id' => $this->primaryKey()->unsigned(),
            'resume_id' => $this->integer()->unsigned()->notNull(),
            'vacancy_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-job_resume_match_resume_id-resume_id',
            '{{%job_resume_match}}',
            'resume_id',
            '{{%resume}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-job_resume_match_vacancy_id-vacancy_id',
            '{{%job_resume_match}}',
            'vacancy_id',
            '{{%vacancy}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-job_resume_match_resume_id-resume_id', '{{%job_resume_match}}');

        $this->dropForeignKey('fk-job_resume_match_vacancy_id-vacancy_id', '{{%job_resume_match}}');

        $this->dropTable('{{%job_resume_match}}');

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
}
