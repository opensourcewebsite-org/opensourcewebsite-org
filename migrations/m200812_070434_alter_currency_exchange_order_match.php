<?php

use yii\db\Migration;

/**
 * Class m200812_070434_alter_currency_exchange_order_match
 */
class m200812_070434_alter_currency_exchange_order_match extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-currency_exchange_order_match_match_order_id-order_id', '{{%currency_exchange_order_match}}');

        $this->addForeignKey(
            'fk-currency_exchange_order_match-match_order_id',
            '{{%currency_exchange_order_match}}',
            'match_order_id',
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
        echo "m200812_070434_alter_currency_exchange_order_match cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200812_070434_alter_currency_exchange_order_match cannot be reverted.\n";

        return false;
    }
    */
}
