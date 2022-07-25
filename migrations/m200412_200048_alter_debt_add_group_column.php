<?php

use yii\db\Migration;

/**
 * Class m200412_200048_alter_debt_add_group_column
 */
class m200412_200048_alter_debt_add_group_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%debt}}', 'group', $this->decimal(19, 4));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%debt}}', 'group');
    }
}
