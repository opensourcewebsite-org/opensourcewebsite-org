<?php

use yii\db\Migration;

/**
 * Class m211019_102342_alter_debt_balance_table
 */
class m211019_102342_alter_debt_balance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%debt_balance}}', 'amount', $this->decimal(15, 2)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%debt_balance}}', 'amount', $this->decimal(15, 2)->unsigned()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211019_102342_alter_debt_balance_table cannot be reverted.\n";

        return false;
    }
    */
}
