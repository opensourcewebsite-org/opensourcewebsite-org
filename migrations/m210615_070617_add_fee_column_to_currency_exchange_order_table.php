<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%currency_exchange_order}}`.
 */
class m210615_070617_add_fee_column_to_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%currency_exchange_order}}', 'fee', $this->decimal(15, 8)->notNull()->after('buying_currency_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%currency_exchange_order}}', 'fee');
    }
}
