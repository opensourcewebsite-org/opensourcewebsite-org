<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%contact}}`.
 */
class m211031_105626_add_is_basic_income_candidate_column_to_contact_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%contact}}', 'is_basic_income_candidate', $this->tinyInteger()->unsigned()->defaultValue(0)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%contact}}', 'is_basic_income_candidate');
    }
}
