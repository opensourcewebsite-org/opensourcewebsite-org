<?php

use yii\db\Migration;

/**
 * Class m220526_031842_add_description_column_to_bot_chat
 */
class m220526_031842_add_description_column_to_bot_chat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat}}', 'description', $this->string()->after('last_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat}}', 'description');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220526_031842_add_description_column_to_bot_chat cannot be reverted.\n";

        return false;
    }
    */
}
