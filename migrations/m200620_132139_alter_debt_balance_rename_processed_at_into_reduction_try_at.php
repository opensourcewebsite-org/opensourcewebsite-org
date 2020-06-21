<?php

use yii\db\Migration;

/**
 * Class m200620_132139_alter_debt_balance_rename_processed_at_into_reduction_try_at
 */
class m200620_132139_alter_debt_balance_rename_processed_at_into_reduction_try_at extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('debt_balance', 'processed_at', 'reduction_try_at');
        $this->update('debt_balance', ['reduction_try_at' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('debt_balance', 'reduction_try_at','processed_at');
        $this->update('debt_balance', ['processed_at' => 1]);
    }
}
