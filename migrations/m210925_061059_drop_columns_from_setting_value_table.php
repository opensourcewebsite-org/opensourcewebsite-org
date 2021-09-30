<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%setting_value}}`.
 */
class m210925_061059_drop_columns_from_setting_value_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('setting_value', 'is_current');
        $this->dropColumn('setting_value', 'updated_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('setting_value', 'is_current', $this->tinyInteger(1)->unsigned()->defaultValue(0)->notNull());
        $this->addColumn('setting_value', 'updated_at', $this->integer()->unsigned());
    }
}
