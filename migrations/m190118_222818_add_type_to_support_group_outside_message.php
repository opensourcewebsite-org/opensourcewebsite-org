<?php

use yii\db\Migration;

/**
 * Class m190118_222818_add_type_to_support_group_outside_message
 */
class m190118_222818_add_type_to_support_group_outside_message extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('support_group_outside_message', 'type', $this->tinyInteger()->notNull()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('support_group_outside_message', 'type');
    }
}
