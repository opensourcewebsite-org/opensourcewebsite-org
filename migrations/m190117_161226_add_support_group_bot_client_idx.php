<?php

use yii\db\Migration;

/**
 * Class m190117_161226_add_support_group_bot_client_idx
 */
class m190117_161226_add_support_group_bot_client_idx extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx-support_group_bot_client-support_group_user_id', 'support_group_bot_client', 'provider_bot_user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-support_group_bot_client-support_group_user_id', 'support_group_bot_client');
    }
}
