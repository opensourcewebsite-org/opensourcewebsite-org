<?php

use yii\db\Migration;

/**
 * Handles the creation of table `cron_job`.
 */
class m181225_192616_create_cron_job_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('cron_job', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(127)->notNull(),
            'description' => $this->text(),
            'status' => $this->smallInteger()->notNull()->notNull(),
            'created_at' => $this->integer()->notNull()->unsigned(),
            'updated_at' => $this->integer()->notNull()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('cron_job');
    }
}
