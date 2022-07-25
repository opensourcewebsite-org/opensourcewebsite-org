<?php

use yii\db\Migration;

/**
 * Class m220527_045055_alter_currency_exchange_order_table
 */
class m220527_045055_alter_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%currency_exchange_order}}', 'fee');

        $this->addColumn('{{%currency_exchange_order}}', 'selling_rate', $this->decimal(16, 8)->after('buying_currency_id'));
        $this->addColumn('{{%currency_exchange_order}}', 'buying_rate', $this->decimal(16, 8)->after('selling_rate'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%currency_exchange_order}}', 'selling_rate');
        $this->dropColumn('{{%currency_exchange_order}}', 'buying_rate');

        $this->addColumn('{{%currency_exchange_order}}', 'fee', $this->decimal(18, 8)->notNull()->after('buying_currency_id'));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220527_045055_alter_currency_exchange_order_table cannot be reverted.\n";

        return false;
    }
    */
}
