<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%vacancy}}`.
 */
class m200622_125105_add_remote_on_column_to_vacancy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%vacancy}}', 'renewed_at', $this->integer()->unsigned()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%vacancy}}', 'renewed_at', $this->integer()->unsigned());
        $this->alterColumn('{{%vacancy}}', 'company_id', $this->integer()->unsigned()->notNull());
        $this->dropColumn('{{%vacancy}}', 'user_id');
        $this->addColumn('{{%vacancy}}', 'location_at', $this->integer()->unsigned());
        $this->dropColumn('{{%vacancy}}', 'remote_on');
        $this->dropColumn('{{%vacancy}}', 'created_at');
        $this->dropColumn('{{%vacancy}}', 'processed_at');
    }
}
