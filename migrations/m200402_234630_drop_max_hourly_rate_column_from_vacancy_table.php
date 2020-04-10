<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%vacancy}}`.
 */
class m200402_234630_drop_max_hourly_rate_column_from_vacancy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%vacancy}}', 'max_hourly_rate');

        $this->renameColumn('{{%vacancy}}', 'min_hourly_rate', 'hourly_rate');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{%vacancy}}', 'hourly_rate', 'min_hourly_rate');

        $this->addColumn('{{%vacancy}}', 'max_hourly_rate', $this->decimal(10,2));
    }
}
