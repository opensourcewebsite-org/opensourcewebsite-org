<?php

use yii\db\Migration;

/**
 * Class m200806_040258_alter_currency_exchange_order_match_table
 */
class m200806_040258_alter_currency_exchange_order_match_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-currency_exchange_order_match_match_oder_id-order_id', '{{%currency_exchange_order_match}}');

        $this->dropForeignKey('fk-currency_exchange_order_match_oder_id-order_id', '{{%currency_exchange_order_match}}');

        $this->addForeignKey(
            'fk-currency_exchange_order_match_order_id-order_id',
            '{{%currency_exchange_order_match}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-currency_exchange_order_match_match_order_id-order_id',
            '{{%currency_exchange_order_match}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-currency_exchange_order_match_match_order_id-order_id', '{{%currency_exchange_order_match}}');

        $this->dropForeignKey('fk-currency_exchange_order_match_order_id-order_id', '{{%currency_exchange_order_match}}');

        $this->addForeignKey(
            'fk-currency_exchange_order_match_oder_id-order_id',
            '{{%currency_exchange_order_match}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id'
        );

        $this->addForeignKey(
            'fk-currency_exchange_order_match_match_oder_id-order_id',
            '{{%currency_exchange_order_match}}',
            'order_id',
            '{{%currency_exchange_order}}',
            'id'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200806_040258_alter_currency_exchange_order_match_table cannot be reverted.\n";

        return false;
    }
    */
}
