<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_response}}`.
 */
class m200526_043023_create_api_test_response_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_response}}', [
            'id' => $this->primaryKey()->unsigned(),
            'request_id' => $this->integer()->unsigned()->notNull()->comment('Request identity'),
            'job_id' => $this->integer()->unsigned()->null()->comment('Job identity'),
            'headers' => $this->text()->comment('Headers'),
            'body' => 'LONGTEXT',
            'cookies' => $this->text()->comment('Cookies'),
            'code' => $this->integer()->unsigned()->notNull()->comment('Response code'),
            'time' => $this->integer()->unsigned()->comment('Request execution time'),
            'size' => $this->integer()->unsigned()->comment('Size of response'),
            'created_at' => $this->integer()->notNull()->comment('Response time')
        ]);

        $this->createIndex(
            'idx-api_test_response-request_id',
            '{{%api_test_response}}',
            'request_id'
        );

        $this->addForeignKey(
            'fk-api_test_response-request_id',
            '{{%api_test_response}}',
            'request_id',
            '{{%api_Test_request}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-api_test_response-job_id',
            '{{%api_test_response}}',
            'job_id'
        );

        $this->addForeignKey(
            'fk-api_test_response-job_id',
            '{{%api_test_response}}',
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
        $this->dropTable('{{%api_test_response}}');
    }
}
