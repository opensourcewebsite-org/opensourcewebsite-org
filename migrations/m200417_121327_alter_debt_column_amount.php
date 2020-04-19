<?php

use yii\db\Migration;

/**
 * Class m200417_121327_alter_debt_column_amount
 */
class m200417_121327_alter_debt_column_amount extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('debt', 'amount', $this->decimal(15, 2)->unsigned()->notNull());
        $this->alterColumn('debt_balance', 'amount', $this->decimal(15, 2)->unsigned()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('debt', 'amount', $this->integer());
        $this->alterColumn('debt_balance', 'amount', $this->decimal()->unsigned()->notNull());
    }
}
