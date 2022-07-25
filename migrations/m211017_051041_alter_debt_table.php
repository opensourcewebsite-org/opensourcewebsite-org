<?php

use yii\db\Migration;

/**
 * Class m211017_051041_alter_debt_table
 */
class m211017_051041_alter_debt_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%debt}}', 'status', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%debt}}', 'status', $this->tinyInteger()->unsigned()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211017_051041_alter_debt_table cannot be reverted.\n";

        return false;
    }
    */
}
