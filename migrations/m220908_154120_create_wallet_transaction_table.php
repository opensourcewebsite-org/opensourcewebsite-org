<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wallet_transaction}}`.
 */
class m220908_154120_create_wallet_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wallet_transaction}}', [
            'id' => $this->primaryKey()->unsigned(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'from_user_id' => $this->integer()->unsigned()->notNull(),
            'to_user_id' => $this->integer()->unsigned()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'type' =>  $this->tinyInteger()->unsigned()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-wallet_transaction-currency_id',
            '{{%wallet_transaction}}',
            'currency_id',
            '{{%currency}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-wallet_transaction-from_user_id',
            '{{%wallet_transaction}}',
            'from_user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-wallet_transaction-to_user_id',
            '{{%wallet_transaction}}',
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
        $this->dropForeignKey(
            'fk-wallet_transaction-from_user_id',
            '{{%wallet_transaction}}'
        );

        $this->dropForeignKey(
            'fk-wallet_transaction-to_user_id',
            '{{%wallet_transaction}}'
        );

        $this->dropTable('{{%wallet_transaction}}');
    }
}
