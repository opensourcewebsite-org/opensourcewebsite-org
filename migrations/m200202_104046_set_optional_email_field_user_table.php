<?php

use yii\db\Migration;

/**
 * Class m200202_104046_set_optional_email_field_user_table
 */
class m200202_104046_set_optional_email_field_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->alterColumn('user', 'email', $this->string()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->alterColumn('user', 'email', $this->string()->notNull()->unique());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200202_104046_set_optional_email_field_user_table cannot be reverted.\n";

        return false;
    }
    */
}
