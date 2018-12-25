<?php

use yii\db\Migration;

/**
 * Class m181225_193152_add_cron_job_id_fk
 */
class m181225_193152_add_cron_job_id_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            '{{%fk-cron_job_log-cron_job_id}}',
            '{{%cron_job_log}}',
            'cron_job_id',
            '{{%cron_job}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-cron_job_log-cron_job_id}}',
            '{{%cron_job_log}}'
        );
    }
}
