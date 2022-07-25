<?php

use yii\db\Migration;

/**
 * Class m200412_194435_create_debt_balance
 */
class m200412_194435_create_debt_balance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%debt_balance}}', [
            'currency_id'  => $this->integer()->unsigned()->notNull(),
            'from_user_id' => $this->integer()->unsigned()->notNull(),
            'to_user_id'   => $this->integer()->unsigned()->notNull(),
            'amount'       => $this->decimal()->unsigned()->notNull(),
            'processed_at' => $this->integer()->unsigned(),
        ]);
        $this->addPrimaryKey('pk_debt_balance_currency_id_from_user_id_to_user_id', '{{%debt_balance}}', ['currency_id', 'from_user_id', 'to_user_id']);
        $this->addForeignKey(
            'fk-debt_balance-currency_id-currency-id',
            '{{%debt_balance}}',
            'currency_id',
            '{{%currency}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-debt_balance-from_user_id-user-id',
            '{{%debt_balance}}',
            'from_user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-debt_balance-to_user_id-user-id',
            '{{%debt_balance}}',
            'to_user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%debt_balance}}');
    }
}
