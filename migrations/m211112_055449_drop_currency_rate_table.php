<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%currency_rate}}`.
 */
class m211112_055449_drop_currency_rate_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%currency_rate}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211112_055449_drop_currency_rate_table cannot be reverted.\n";

        return false;
    }
}
