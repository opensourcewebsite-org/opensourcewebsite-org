<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_vacancy_match}}`.
 */
class m200806_040211_create_job_vacancy_match_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job_vacancy_match}}', [
            'id' => $this->primaryKey()->unsigned(),
            'vacancy_id' => $this->integer()->unsigned()->notNull(),
            'resume_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-job_vacancy_match_resume_id-resume_id',
            '{{%job_vacancy_match}}',
            'resume_id',
            '{{%resume}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-job_vacancy_match_vacancy_id-vacancy_id',
            '{{%job_vacancy_match}}',
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
        $this->dropForeignKey('fk-job_vacancy_match_resume_id-resume_id', '{{%job_vacancy_match}}');

        $this->dropForeignKey('fk-job_vacancy_match_vacancy_id-vacancy_id', '{{%job_vacancy_match}}');

        $this->dropTable('{{%job_vacancy_match}}');
    }
}
