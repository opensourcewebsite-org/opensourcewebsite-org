<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wallet}}`.
 */
class m220908_145648_create_wallet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wallet}}', [
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
        ]);

        $this->addPrimaryKey(
            'pk-wallet-currency_id-user_id',
            '{{%wallet}}',
            ['currency_id', 'user_id']
        );

        $this->addForeignKey(
            'fk-wallet-currency_id',
            '{{%wallet}}',
            'currency_id',
            '{{%currency}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-wallet-user_id',
            '{{%wallet}}',
            'user_id',
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
            'fk-wallet-currency_id',
            '{{%wallet}}'
        );

        $this->dropForeignKey(
            'fk-wallet-user_id',
            '{{%wallet}}'
        );

        $this->dropTable('{{%wallet}}');
    }
}
