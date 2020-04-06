<?php

use yii\db\Migration;

/**
 * Class m200404_120641_alter_contact_add_debt_redistribution_priority_column
 */
class m200404_120641_alter_contact_add_debt_redistribution_priority_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'contact',
            'debt_redistribution_priority',
            $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('contact', 'debt_redistribution_priority');
    }
}
