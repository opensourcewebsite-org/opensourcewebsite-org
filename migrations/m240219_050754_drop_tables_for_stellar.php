<?php

use yii\db\Migration;

/**
 * Class m240219_050754_drop_tables_for_stellar
 */
class m240219_050754_drop_tables_for_stellar extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%stellar_distributor}}');
        $this->dropTable('{{%user_stellar_income}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240219_050754_drop_tables_for_stellar cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240219_050754_drop_tables_for_stellar cannot be reverted.\n";

        return false;
    }
    */
}
