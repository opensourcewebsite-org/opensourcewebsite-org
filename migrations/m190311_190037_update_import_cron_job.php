<?php

use yii\db\Migration;

/**
 * Class m190311_190037_update_import_cron_job
 */
class m190311_190037_update_import_cron_job extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%cron_job}}', [
            'name' => 'WikinewsParser',
            'status' => '1',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190311_190037_update_import_cron_job cannot be reverted.\n";

        return false;
    }
}
