<?php

use yii\db\Migration;

/**
 * Class m230519_070959_drop_tables_for_stellar
 */
class m230519_070959_drop_tables_for_stellar extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%user}}', 'basic_income_on');
        $this->dropColumn('{{%user}}', 'basic_income_activated_at');
        $this->dropColumn('{{%user}}', 'basic_income_processed_at');
        $this->dropColumn('{{%contact}}', 'is_basic_income_candidate');
        $this->dropTable('{{%user_stellar_basic_income}}');
        $this->dropTable('{{%user_stellar}}');
        $this->dropTable('{{%stellar_giver}}');
        $this->dropTable('{{%stellar_croupier}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230519_070959_drop_tables_for_stellar cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230519_070959_drop_tables_for_stellar cannot be reverted.\n";

        return false;
    }
    */
}
