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
            'request_id' => $this->integer()->unsigned()->notNull(),
            'job_id' => $this->integer()->unsigned()->null(),
            'headers' => $this->text(),
            'body' => 'LONGTEXT',
            'cookies' => $this->text(),
            'code' => $this->integer()->unsigned()->notNull(),
            'time' => $this->integer()->unsigned(),
            'size' => $this->integer()->unsigned(),
            'created_at' => $this->integer()->notNull()
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
