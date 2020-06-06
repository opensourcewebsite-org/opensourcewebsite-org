<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_runner}}`.
 */
class m200526_094207_create_api_test_runner_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_runner}}', [
            'id' => $this->primaryKey(),
            'job_id' => $this->integer()->unsigned(),
            'request_id' => $this->integer()->unsigned(),
            'triggered_by' => $this->integer()->unsigned(),
            'triggered_by_schedule' => $this->integer()->unsigned(),
            'timing' => $this->integer()->unsigned(),
            'status' => $this->tinyInteger()->unsigned(),
            'start_at' => $this->integer()->unsigned()
        ]);

        $this->createIndex(
            'ix-api_test_runner-job_id',
            '{{%api_test_runner}}',
            'job_id'
        );

        $this->addForeignKey(
            'fk-api_test_runner-job_id',
            '{{%api_test_runner}}',
            'job_id',
            '{{%api_test_job}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'ix-api_test_runner-request_id',
            '{{%api_test_runner}}',
            'request_id'
        );

        $this->addForeignKey(
            'fk-api_test_runner-request_id',
            '{{%api_test_runner}}',
            'request_id',
            '{{%api_test_request}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'ix-api_test_runner-schedule',
            '{{%api_test_runner}}',
            'triggered_by_schedule'
        );

        $this->addForeignKey(
            'fk-api_test_runner-schedule',
            '{{%api_test_runner}}',
            'triggered_by_schedule',
            '{{%api_test_job_schedule}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%api_test_runner}}');
    }
}
