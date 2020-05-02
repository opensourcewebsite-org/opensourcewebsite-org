<?php

use yii\db\Migration;

/**
 * Class m200417_142101_add_optional_name_column_to_currency_exchange_order
 */
class m200417_142101_add_optional_name_column_to_currency_exchange_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('currency_exchange_order', 'optional_neme', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('currency_exchange_order', 'optional_neme', $this->string());
    }

}
