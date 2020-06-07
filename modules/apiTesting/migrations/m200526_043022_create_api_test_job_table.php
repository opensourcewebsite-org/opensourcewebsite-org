<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_job}}`.
 */
class m200526_043022_create_api_test_job_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_job}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer()->unsigned(),
            'name' => $this->string()->notNull(),
        ]);

        $this->createIndex(
            'ix-api_test_job-project_id',
            '{{%api_test_job}}',
            'project_id'
        );

        $this->addForeignKey(
            'fk-api_test_job-project_id',
            '{{%api_test_job}}',
            'project_id',
            '{{%api_test_project}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%api_test_job}}');
    }
}
