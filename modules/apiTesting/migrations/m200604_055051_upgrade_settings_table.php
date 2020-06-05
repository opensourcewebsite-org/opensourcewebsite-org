<?php

use app\models\Setting;
use yii\db\Migration;

/**
 * Class m200604_055051_upgrade_settings_table
 */
class m200604_055051_upgrade_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert(Setting::tableName(), [
            'key' => 'api_tester_project_quantity_value_per_one_rating',
            'value' => '0.01',
            'updated_at' => time()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete(Setting::tableName(), [
            'key' => 'api_tester_project_quantity_value_per_one_rating'
        ]);
    }
}
