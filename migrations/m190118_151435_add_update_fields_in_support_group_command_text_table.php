<?php

use yii\db\Migration;

/**
 * Class m190118_151435_add_update_fields_in_support_group_command_text_table
 */
class m190118_151435_add_update_fields_in_support_group_command_text_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('support_group_command_text', 'updated_at', $this->integer()->unsigned()->notNull());
        $this->addColumn('support_group_command_text', 'updated_by', $this->integer()->unsigned()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('support_group_command_text', 'updated_at');
        $this->dropColumn('support_group_command_text', 'updated_by');
    }
}
