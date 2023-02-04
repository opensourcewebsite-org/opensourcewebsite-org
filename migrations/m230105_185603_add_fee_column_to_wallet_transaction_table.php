<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%wallet_transaction}}`.
 */
class m230105_185603_add_fee_column_to_wallet_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%wallet_transaction}}', 'fee', $this->decimal(15, 2)->notNull()->after('amount'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%wallet_transaction}}', 'fee');
    }
}
