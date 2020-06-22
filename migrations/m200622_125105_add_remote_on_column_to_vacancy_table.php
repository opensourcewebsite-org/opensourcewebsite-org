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
        $this->dropColumn('{{%vacancy}}', 'location_at');
        $this->addColumn('{{%vacancy}}', 'remote_on', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('{{%vacancy}}', 'created_at', $this->integer()->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('{{%vacancy}}', 'processed_at', $this->integer()->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%vacancy}}', 'location_at', $this->integer()->unsigned());
        $this->dropColumn('{{%vacancy}}', 'remote_on');
        $this->dropColumn('{{%vacancy}}', 'created_at');
        $this->dropColumn('{{%vacancy}}', 'processed_at');
    }
}
