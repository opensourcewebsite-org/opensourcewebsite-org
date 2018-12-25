<?php

use yii\db\Migration;

/**
 * Handles the creation of table `cron_job_log`.
 */
class m181225_191743_create_cron_job_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('cron_job_log', [
            'id' => $this->primaryKey()->unsigned(),
            'message' => $this->string(255)->notNull(),
            'cron_job_id' => $this->integer(11)->notNull()->unsigned(),
            'created_at' => $this->integer()->notNull()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('cron_job_log');
    }
}
