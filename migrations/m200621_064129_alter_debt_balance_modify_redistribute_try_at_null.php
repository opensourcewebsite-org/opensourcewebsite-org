<?php

use yii\db\Migration;

/**
 * Class m200621_064129_alter_debt_balance_modify_redistribute_try_at_null
 */
class m200621_064129_alter_debt_balance_modify_redistribute_try_at_null extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(
            'debt_balance',
            'redistribute_try_at',
            $this->integer()->unsigned()
        );
        $this->update('debt_balance', ['redistribute_try_at' => null], 'redistribute_try_at = 0');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn(
            'debt_balance',
            'redistribute_try_at',
            $this->integer()->unsigned()->notNull()->defaultValue(0)
        );
    }
}
