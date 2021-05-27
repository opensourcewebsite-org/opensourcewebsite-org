<?php

use yii\db\Migration;

/**
 * Class m210527_090857_add_foreign_keys_to_job_vacancy_keyword
 */
class m210527_090857_add_foreign_keys_to_job_vacancy_keyword extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'job-vacancy-keyword_vacancy_fk',
            '{{%job_vacancy_keyword}}',
            'vacancy_id',
            '{{%vacancy}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'job-vacancy-keyword_job_keyword_fk',
            '{{%job_vacancy_keyword}}',
            'job_keyword_id',
            '{{%job_keyword}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('job-vacancy-keyword_vacancy_fk', '{{%job_vacancy_keyword}}');
        $this->dropForeignKey('job-vacancy-keyword_job_keyword_fk', '{{%job_vacancy_keyword}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210527_090857_add_foreign_keys_to_job_vacancy_keyword cannot be reverted.\n";

        return false;
    }
    */
}
