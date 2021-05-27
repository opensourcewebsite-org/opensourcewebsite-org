<?php

use yii\db\Migration;

/**
 * Class m210527_090155_add_foreign_keys_to_job_resume_keyword
 */
class m210527_090155_add_foreign_keys_to_job_resume_keyword extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'job-resume-keyword_resume_fk',
            '{{%job_resume_keyword}}',
            'resume_id',
            '{{%resume}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'job-resume-keyword_job_keyword_fk',
            '{{%job_resume_keyword}}',
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
        $this->dropForeignKey('job-resume-keyword_resume_fk', '{{%job_resume_keyword}}');
        $this->dropForeignKey('job-resume-keyword_job_keyword_fk', '{{%job_resume_keyword}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210527_090155_add_foreign_keys_to_job_resume_keyword cannot be reverted.\n";

        return false;
    }
    */
}
