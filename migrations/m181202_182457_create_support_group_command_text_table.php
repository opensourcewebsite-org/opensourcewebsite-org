<?php

use yii\db\Migration;

/**
 * Handles the creation of table `support_group_command_text`.
 */
class m181202_182457_create_support_group_command_text_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('support_group_command_text', [
            'id' => $this->primaryKey()->unsigned(),
            'support_group_command_id' => $this->integer()->unsigned()->notNull(),
            'language_id' => $this->integer()->unsigned()->notNull(),
            'text' => $this->text()->notNull(),
        ]);

        $this->createIndex(
            'idx-support_group_command_text-support_group_command_id',
            'support_group_command_text',
            'support_group_command_id'
        );

        $this->addForeignKey(
            'fk-support_group_command_text-support_group_command_id',
            'support_group_command_text',
            'support_group_command_id',
            'support_group_command',
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
            'fk-support_group_command_text-support_group_command_id',
            'support_group_command_text'
        );

        $this->dropIndex(
            'idx-support_group_command_text-support_group_command_id',
            'support_group_command_text'
        );

        $this->dropTable('support_group_command_text');
    }
}
