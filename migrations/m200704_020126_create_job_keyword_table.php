<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%job_keyword}}`.
 */
class m200704_020126_create_job_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('job_keyword', [
            'id' => $this->primaryKey()->unsigned(),
            'keyword' => $this->string()->notNull()->unique(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('job_keyword');
    }
}
