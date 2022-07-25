<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m211105_075406_add_columns_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'basic_income_activated_at', $this->integer()->unsigned());
        $this->addColumn('{{%user}}', 'basic_income_processed_at', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'basic_income_activated_at');
        $this->dropColumn('{{%user}}', 'basic_income_processed_at');
    }
}
