<?php

use yii\db\Migration;

/**
 * Handles the creation of table `support_group_command`.
 */
class m181202_182054_create_support_group_command_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('support_group_command', [
            'id' => $this->primaryKey()->unsigned(),
            'support_group_id' => $this->integer()->unsigned()->notNull(),
            'command' => $this->string()->notNull(),
            'is_default' => $this->boolean()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
            'updated_by' => $this->integer()->unsigned()->notNull()
        ]);

        $this->createIndex(
            'idx-support_group_command-support_group_id',
            'support_group_command',
            'support_group_id'
        );

        $this->addForeignKey(
            'fk-support_group_command-support_group_id',
            'support_group_command',
            'support_group_id',
            'support_group',
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
            'fk-support_group_command-support_group_id',
            'support_group_command'
        );

        $this->dropIndex(
            'idx-support_group_command-support_group_id',
            'support_group_command'
        );

        $this->dropTable('support_group_command');
    }
}
