<?php

use app\models\WalletTransaction;
use yii\db\Migration;
use yii\db\Query;

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

        $rows = (new Query())
            ->select([
                't.id as transaction_id',
                'c.id as chat_tip_id',
            ])
            ->from('bot_chat_tip_wallet_transaction as c')
            ->join('JOIN', 'wallet_transaction t', 't.id = c.transaction_id');

        foreach ($rows->each(1) as $row) {
            $this->update(
                '{{%wallet_transaction}}',
                [
                    'data' => [
                        WalletTransaction::CHAT_TIP_ID_DATA_KEY => $row['chat_tip_id'],
                    ],
                ],
                'id = :transaction_id',
                [':transaction_id' => $row['transaction_id']]
            );
        }
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
