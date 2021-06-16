<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%currency_exchange_order}}`.
 */
class m210615_153326_drop_columns_from_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%currency_exchange_order}}', 'cross_rate_on');
        $this->dropColumn('{{%currency_exchange_order}}', 'selling_rate');
        $this->dropColumn('{{%currency_exchange_order}}', 'buying_rate');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%currency_exchange_order}}', 'cross_rate_on', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('{{%currency_exchange_order}}', 'selling_rate', $this->decimal(15, 8)->defaultValue(null));
        $this->addColumn('{{%currency_exchange_order}}', 'buying_rate', $this->decimal(15, 8)->defaultValue(null));
    }
}
