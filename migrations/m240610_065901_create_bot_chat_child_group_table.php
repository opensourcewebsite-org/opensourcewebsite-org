<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_child_group}}`.
 */
class m240610_065901_create_bot_chat_child_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_child_group}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'child_group_id' => $this->integer()->unsigned()->notNull(),
            'updated_by' => $this->integer()->unsigned()->notNull()
        ]);

        $this->addForeignKey(
            '{{%fk-bot_chat_child_group-chat_id}}',
            '{{%bot_chat_child_group}}',
            'chat_id',
            '{{%bot_chat}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add foreign key for table `{{%chat}}` (child group)
        $this->addForeignKey(
            '{{%fk-bot_chat_child_group-child_group_id}}',
            '{{%bot_chat_child_group}}',
            'child_group_id',
            '{{%bot_chat}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add foreign key for table `{{%user}}` (updated_by)
        $this->addForeignKey(
            '{{%fk-bot_chat_child_group-updated_by}}',
            '{{%bot_chat_child_group}}',
            'updated_by',
            '{{%bot_user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-bot_chat_child_group-chat_id}}',
            '{{%bot_chat_child_group}}'
        );

        $this->dropForeignKey(
            '{{%fk-bot_chat_child_group-child_group_id}}',
            '{{%bot_chat_child_group}}'
        );

        $this->dropForeignKey(
            '{{%fk-bot_chat_child_group-updated_by}}',
            '{{%bot_chat_child_group}}'
        );

        $this->dropTable('{{%bot_chat_child_group}}');
    }
}
