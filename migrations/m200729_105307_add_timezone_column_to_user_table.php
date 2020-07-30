<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m200729_105307_add_timezone_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%user}}', 'timezone');
        $this->addColumn('{{%user}}', 'timezone', $this->smallInteger()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'timezone');
        $this->addColumn('{{%user}}', 'timezone', $this->string()->notNull()->defaultValue('UTC'));
    }
}
