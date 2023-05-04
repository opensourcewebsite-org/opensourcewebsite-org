<?php

use yii\db\Migration;

/**
 * Class m230504_000425_create_data_column_in_wallet_transaction
 */
class m230504_000425_create_data_column_in_wallet_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('wallet_transaction', 'data', $this->json()->after('fee'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('wallet_transaction', 'data');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230504_000425_create_data_column_in_wallet_transaction cannot be reverted.\n";

        return false;
    }
    */
}
