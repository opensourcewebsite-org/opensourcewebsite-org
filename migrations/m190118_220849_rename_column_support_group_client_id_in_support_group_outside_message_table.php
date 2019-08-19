<?php

use yii\db\Migration;

/**
 * Class m190118_220849_rename_column_support_group_client_id_in_support_group_outside_message_table
 */
class m190118_220849_rename_column_support_group_client_id_in_support_group_outside_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->dropForeignKey(
            'fk-support_group_outside_message-support_group_client_id',
            'support_group_outside_message'
        );

        $this->dropIndex(
            'idx-support_group_outside_message-support_group_client_id',
            'support_group_outside_message'
        );

        $this->renameColumn('support_group_outside_message', 'support_group_client_id', 'support_group_bot_client_id');

        $this->createIndex(
            'idx-support_group_outside_message-support_group_bot_client_id',
            'support_group_outside_message',
            'support_group_bot_client_id'
        );

        $this->addForeignKey(
            'fk-support_group_outside_message-support_group_bot_client_id',
            'support_group_outside_message',
            'support_group_bot_client_id',
            'support_group_bot_client',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-support_group_outside_message-support_group_bot_client_id',
            'support_group_outside_message'
        );

        $this->dropIndex(
            'idx-support_group_outside_message-support_group_bot_client_id',
            'support_group_outside_message'
        );


        $this->renameColumn('support_group_outside_message', 'support_group_bot_client_id', 'support_group_client_id');

        $this->createIndex(
            'idx-support_group_outside_message-support_group_client_id',
            'support_group_outside_message',
            'support_group_client_id'
        );

        $this->addForeignKey(
            'fk-support_group_outside_message-support_group_client_id',
            'support_group_outside_message',
            'support_group_client_id',
            'support_group_client',
            'id',
            'CASCADE'
        );
    }
}
