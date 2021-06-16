<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%currency_exchange_order}}`.
 */
class m210616_021258_add_label_column_to_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%currency_exchange_order}}', 'label', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%currency_exchange_order}}', 'label');
    }
}
