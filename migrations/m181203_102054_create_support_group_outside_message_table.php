<?php

use yii\db\Migration;

/**
 * Handles the creation of table `support_group_outside_message`.
 */
class m181203_102054_create_support_group_outside_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('support_group_outside_message', [
            'id' => $this->primaryKey()->unsigned(),
            'support_group_bot_id' => $this->integer()->unsigned()->notNull(),
            'support_group_client_id' => $this->integer()->unsigned()->notNull(),
            'provider_message_id' => $this->integer()->unsigned(),
            'message' => $this->text()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ]);

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

        $this->createIndex(
            'idx-support_group_outside_message-support_group_bot_id',
            'support_group_outside_message',
            'support_group_bot_id'
        );

        $this->addForeignKey(
            'fk-support_group_outside_message-support_group_bot_id',
            'support_group_outside_message',
            'support_group_bot_id',
            'support_group_bot',
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
            'fk-support_group_outside_message-support_group_client_id',
            'support_group_outside_message'
        );

        $this->dropIndex(
            'idx-support_group_outside_message-support_group_client_id',
            'support_group_outside_message'
        );


        $this->dropForeignKey(
            'fk-support_group_outside_message-support_group_bot_id',
            'support_group_outside_message'
        );

        $this->dropIndex(
            'idx-support_group_outside_message-support_group_bot_id',
            'support_group_outside_message'
        );

        $this->dropTable('support_group_outside_message');
    }
}
