<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_job_request}}`.
 */
class m200526_043704_create_api_test_job_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_job_request}}', [
            'job_id' => $this->integer()->unsigned()->notNull()->comment('Job identity'),
            'request_id' => $this->integer()->unsigned()->notNull()->comment('Request identity')
        ]);

        $this->addPrimaryKey('pk-test-job-request', '{{%api_test_job_request}}', ['job_id', 'request_id']);

        $this->addForeignKey(
            'fk-api_test_job_request-request_id',
            '{{%api_test_job_request}}',
            'request_id',
            '{{%api_test_request}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-api_test_job_request-job_id',
            '{{%api_test_job_request}}',
            'job_id',
            '{{%api_test_job}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%api_test_job_request}}');
    }
}
