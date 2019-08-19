<?php

use yii\db\Migration;

/**
 * Handles the creation of table `support_group_client_bot`.
 */
class m181202_190910_create_support_group_client_bot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('support_group_client_bot', [
            'id' => $this->primaryKey()->unsigned(),
            'support_group_bot_id' => $this->integer()->unsigned()->notNull(),
            'support_group_client_id' => $this->integer()->unsigned()->notNull(),
            'provider_bot_user_id' => $this->integer()->unsigned()->notNull(),
            'provider_bot_user_name' => $this->string(),
            'provider_bot_user_blocked' => $this->boolean()->notNull(),
        ]);

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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
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

        $this->dropTable('support_group_client_bot');
    }
}
