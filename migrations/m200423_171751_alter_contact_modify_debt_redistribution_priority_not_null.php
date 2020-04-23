<?php

use yii\db\Migration;

/**
 * Class m200423_171751_alter_contact_modify_debt_redistribution_priority_not_null
 */
class m200423_171751_alter_contact_modify_debt_redistribution_priority_not_null extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('contact', 'debt_redistribution_priority', $this->tinyInteger()->unsigned()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('contact', 'debt_redistribution_priority', $this->tinyInteger()->unsigned());
    }
}
