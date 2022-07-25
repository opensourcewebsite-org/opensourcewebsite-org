<?php

use yii\db\Migration;

/**
 * Class m211029_133632_add_basic_income_on_column_to_user_table
 */
class m211029_133632_add_basic_income_on_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'basic_income_on', $this->tinyInteger()->unsigned()->notNull()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'basic_income_on');
    }
}
