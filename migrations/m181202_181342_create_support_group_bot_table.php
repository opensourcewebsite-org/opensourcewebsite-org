<?php

use yii\db\Migration;

/**
 * Handles the creation of table `support_group_bot`.
 */
class m181202_181342_create_support_group_bot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('support_group_bot', [
            'id' => $this->primaryKey()->unsigned(),
            'support_group_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string()->notNull(),
            'token' => $this->string()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
            'updated_by' => $this->integer()->unsigned()->notNull()
        ]);

        $this->createIndex(
            'idx-support_group_bot-support_group_id',
            'support_group_bot',
            'support_group_id'
        );

        $this->addForeignKey(
            'fk-support_group_bot-support_group_id',
            'support_group_bot',
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
            'fk-support_group_bot-support_group_id',
            'support_group_bot'
        );

        $this->dropIndex(
            'idx-support_group_bot-support_group_id',
            'support_group_bot'
        );

        $this->dropTable('support_group_bot');
    }
}
