<?php

use yii\db\Migration;

/**
 * Class m190121_141642_add_last_message_at_to_support_group_bot_client_table
 */
class m190121_141642_add_last_message_at_to_support_group_bot_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('support_group_bot_client', 'last_message_at', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('support_group_bot_client', 'last_message_at');
    }
}
