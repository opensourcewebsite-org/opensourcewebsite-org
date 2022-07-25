<?php

use yii\db\Migration;

/**
 * Class m200522_145501_alter_contact_modify_debt_redistribution_priority_column_set_default
 */
class m200522_145501_alter_contact_modify_debt_redistribution_priority_column_set_default extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            'contact',
            ['debt_redistribution_priority' => 0],
            ['debt_redistribution_priority' => null]
        );

        $this->alterColumn('contact', 'debt_redistribution_priority', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('contact', 'debt_redistribution_priority', $this->tinyInteger()->unsigned());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200522_145501_alter_contact_modify_debt_redistribution_priority_column_set_default cannot be reverted.\n";

        return false;
    }
    */
}
