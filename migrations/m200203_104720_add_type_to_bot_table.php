<?php

use yii\db\Migration;

/**
 * Class m200203_104720_add_type_to_bot_table
 */
class m200203_104720_add_type_to_bot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('bot', 'type', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('bot', 'type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200203_104720_add_type_to_bot_table cannot be reverted.\n";

        return false;
    }
    */
}
