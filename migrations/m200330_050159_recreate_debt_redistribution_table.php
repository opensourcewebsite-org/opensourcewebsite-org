<?php

use yii\db\Migration;

/**
 * Class m200330_050159_recreate_debt_redistribution_table
 */
class m200330_050159_recreate_debt_redistribution_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%debt_redistribution}}');

        $this->createTable('{{%debt_redistribution}}', [
            'id'           => $this->primaryKey()->unsigned(),
            'from_user_id' => $this->integer()->unsigned()->notNull(),
            'to_user_id'   => $this->integer()->unsigned()->notNull(),
            'currency_id'   => $this->integer()->unsigned()->notNull(),
            'max_amount'   => $this->decimal()->unsigned()->defaultValue(0),
            //'priority'     => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0), //todo [#294][priority] add it into `contact` later in new migration
        ]);

        $this->createIndex(
            'uidx-debt_redistribution-from_user_id-to_user_id-currency_id',
            '{{%debt_redistribution}}',
            ['from_user_id', 'to_user_id', 'currency_id'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%debt_redistribution}}');

        require 'm200326_202143_create_debt_redistribution_table.php';
        m200326_202143_create_debt_redistribution_table::createDebtRedistribution($this);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200330_050159_recreate_debt_redistribution_table cannot be reverted.\n";

        return false;
    }
    */
}
