<?php

use yii\db\Migration;

/**
 * Class m200417_095924_drop_debt_columns_valid_from_date
 */
class m200417_095924_drop_debt_columns_valid_from_date extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('debt', 'valid_from_date');
        $this->dropColumn('debt', 'valid_from_time');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('debt', 'valid_from_date', $this->date());
        $this->addColumn('debt', 'valid_from_time', $this->time());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200417_095924_drop_debt_columns_valid_from_date cannot be reverted.\n";

        return false;
    }
    */
}
