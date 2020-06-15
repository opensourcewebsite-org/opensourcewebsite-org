<?php

use yii\db\Migration;

/**
 * Class m200615_082530_alter_debt_redistribution_modify_max_amount_decimal_precision
 */
class m200615_082530_alter_debt_redistribution_modify_max_amount_decimal_scale extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('debt_redistribution', 'max_amount', $this->decimal(15, 2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('debt_redistribution', 'max_amount', $this->decimal());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200615_082530_alter_debt_redistribution_modify_max_amount_decimal_precision cannot be reverted.\n";

        return false;
    }
    */
}
