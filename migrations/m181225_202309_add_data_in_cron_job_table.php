<?php

use yii\db\Migration;

/**
 * Class m181225_202309_add_data_in_cron_job_table
 */
class m181225_202309_add_data_in_cron_job_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $data = [
            'id' => 1,
            'name' => 'wiki-parser',
            'description' => 'Watchlist parser from wikipedia in different languages',
            'status' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $this->insert('{{%cron_job}}', $data);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%cron_job}}', ['id' => 1]);
    }

}
