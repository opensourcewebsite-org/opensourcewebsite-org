<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%wallet_transaction}}`.
 */
class m230104_185907_add_anonymity_column_to_wallet_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%wallet_transaction}}', 'anonymity', $this->tinyInteger()->unsigned()->notNull()->after('type'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%wallet_transaction}}', 'anonymity');
    }
}
