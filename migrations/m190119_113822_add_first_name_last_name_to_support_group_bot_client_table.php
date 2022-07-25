<?php

use yii\db\Migration;

/**
 * Class m190119_113822_add_first_name_last_name_to_support_group_bot_client_table
 */
class m190119_113822_add_first_name_last_name_to_support_group_bot_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('support_group_bot_client', 'provider_bot_user_first_name', $this->string());
        $this->addColumn('support_group_bot_client', 'provider_bot_user_last_name', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('support_group_bot_client', 'provider_bot_user_first_name');
        $this->dropColumn('support_group_bot_client', 'provider_bot_user_last_name');
    }
}
