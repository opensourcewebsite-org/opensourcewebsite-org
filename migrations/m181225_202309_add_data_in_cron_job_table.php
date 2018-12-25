<?php

use yii\db\Migration;
use app\models\CronJob;

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
        echo "m181225_202309_add_data_in_cron_job_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181225_202309_add_data_in_cron_job_table cannot be reverted.\n";

        return false;
    }
    */
}
