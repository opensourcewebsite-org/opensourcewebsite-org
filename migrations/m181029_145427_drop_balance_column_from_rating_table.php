<?php

use yii\db\Migration;

/**
 * Handles dropping balance from table `rating`.
 */
class m181029_145427_drop_balance_column_from_rating_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('rating', 'balance');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('rating', 'balance', $this->integer()->notNull()->after('amount'));
    }
}
