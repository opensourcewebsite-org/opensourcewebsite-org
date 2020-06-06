<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_job_schedule}}`.
 */
class m200526_044007_create_api_test_job_schedule_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_job_schedule}}', [
            'id' => $this->primaryKey()->unsigned(),
            'job_id' => $this->integer()->unsigned()->notNull(),
            'status' => $this->boolean()->notNull()->defaultValue(0),
            'schedule_periodicity' => $this->integer()->unsigned()->notNull(),
            'custom_schedule_from_date' => $this->integer()->unsigned(),
            'custom_schedule_end_date' => $this->integer()->unsigned(),
            'description' => $this->string()->notNull()->defaultValue('')
        ]);

        $this->createIndex(
            'ix-api_test_job_schedule-job_id',
            '{{%api_test_job_schedule}}',
            'job_id'
        );

        $this->addForeignKey(
            'fk-api_test_job_schedule-job_id',
            '{{%api_test_job_schedule}}',
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
        $this->dropTable('{{%api_test_job_schedule}}');
    }
}
