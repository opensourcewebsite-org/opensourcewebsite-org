<?php

use yii\db\Migration;

/**
 * Class m200604_215917_alter_value_in_bot_user_setting_table
 */
class m200604_215917_alter_value_in_bot_user_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%bot_user_setting}}', 'value', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200604_215917_alter_value_in_bot_user_setting_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200604_215917_alter_value_in_bot_user_setting_table cannot be reverted.\n";

        return false;
    }
    */
}
