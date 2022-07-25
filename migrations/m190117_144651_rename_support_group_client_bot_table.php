<?php

use yii\db\Migration;

/**
 * Class m190117_144651_rename_support_group_client_bot_table
 */
class m190117_144651_rename_support_group_client_bot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        # drop it first
        $this->dropForeignKey(
            'fk-support_group_client_bot-support_group_bot_id',
            'support_group_client_bot'
        );

        $this->dropIndex(
            'idx-support_group_client_bot-support_group_bot_id',
            'support_group_client_bot'
        );

        $this->dropForeignKey(
            'fk-support_group_client_bot-support_group_client_id',
            'support_group_client_bot'
        );

        $this->dropIndex(
            'idx-support_group_client_bot-support_group_client_id',
            'support_group_client_bot'
        );

        $this->renameTable('support_group_client_bot', 'support_group_bot_client');


        # create new
        $this->createIndex(
            'idx-support_group_bot_client-support_group_bot_id',
            'support_group_bot_client',
            'support_group_bot_id'
        );

        $this->addForeignKey(
            'fk-support_group_bot_client-support_group_bot_id',
            'support_group_bot_client',
            'support_group_bot_id',
            'support_group_bot',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-support_group_bot_client-support_group_client_id',
            'support_group_bot_client',
            'support_group_client_id'
        );

        $this->addForeignKey(
            'fk-support_group_bot_client-support_group_client_id',
            'support_group_bot_client',
            'support_group_client_id',
            'support_group_client',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        # drop it first
        $this->dropForeignKey(
            'fk-support_group_bot_client-support_group_bot_id',
            'support_group_bot_client'
        );

        $this->dropIndex(
            'idx-support_group_bot_client-support_group_bot_id',
            'support_group_bot_client'
        );

        $this->dropForeignKey(
            'fk-support_group_bot_client-support_group_client_id',
            'support_group_bot_client'
        );

        $this->dropIndex(
            'idx-support_group_bot_client-support_group_client_id',
            'support_group_bot_client'
        );

        $this->renameTable('support_group_bot_client', 'support_group_client_bot');

        # create new
        $this->createIndex(
            'idx-support_group_client_bot-support_group_bot_id',
            'support_group_client_bot',
            'support_group_bot_id'
        );

        $this->addForeignKey(
            'fk-support_group_client_bot-support_group_bot_id',
            'support_group_client_bot',
            'support_group_bot_id',
            'support_group_bot',
            'id',
            'CASCADE'
        );


        $this->createIndex(
            'idx-support_group_client_bot-support_group_client_id',
            'support_group_client_bot',
            'support_group_client_id'
        );

        $this->addForeignKey(
            'fk-support_group_client_bot-support_group_client_id',
            'support_group_client_bot',
            'support_group_client_id',
            'support_group_client',
            'id',
            'CASCADE'
        );
    }
}
