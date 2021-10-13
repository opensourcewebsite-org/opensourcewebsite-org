<?php

use yii\db\Migration;

/**
 * Class m211013_061658_alter_currency_exchange_order_table
 */
class m211013_061658_alter_currency_exchange_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%currency_exchange_order}}', 'label', $this->string()->after('selling_currency_id'));
        $this->renameColumn('{{%currency_exchange_order}}', 'label', 'selling_currency_label');
        $this->addColumn('{{%currency_exchange_order}}', 'buying_currency_label', $this->string()->after('buying_currency_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%currency_exchange_order}}', 'buying_currency_label');
        $this->renameColumn('{{%currency_exchange_order}}', 'selling_currency_label', 'label');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211013_061658_alter_currency_exchange_order_table cannot be reverted.\n";

        return false;
    }
    */
}
