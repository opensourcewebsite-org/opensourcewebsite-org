<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%currency_exchange_order}}`.
 */
class m200713_032256_add_cross_rate_column_to_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('currency_exchange_order', 'cross_rate_on', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('currency_exchange_order', 'cross_rate_on');
    }
}
