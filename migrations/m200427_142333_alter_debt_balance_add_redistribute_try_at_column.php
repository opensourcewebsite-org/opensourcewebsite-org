<?php

use yii\db\Migration;

/**
 * Class m200427_142333_alter_debt_balance_add_redistribute_try_at_column
 */
class m200427_142333_alter_debt_balance_add_redistribute_try_at_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'debt_balance',
            'redistribute_try_at',
            $this->integer()->unsigned()->notNull()->defaultValue(0)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('debt_balance', 'redistribute_try_at');
    }
}
