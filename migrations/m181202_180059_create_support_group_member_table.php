<?php

use yii\db\Migration;

/**
 * Handles the creation of table `support_group_member`.
 */
class m181202_180059_create_support_group_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('support_group_member', [
            'id' => $this->primaryKey()->unsigned(),
            'support_group_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
            'updated_by' => $this->integer()->unsigned()->notNull()
        ]);

        $this->createIndex(
            'idx-support_group_member-support_group_id',
            'support_group_member',
            'support_group_id'
        );

        $this->addForeignKey(
            'fk-support_group_member-support_group_id',
            'support_group_member',
            'support_group_id',
            'support_group',
            'id',
            'CASCADE'
        );


        $this->createIndex(
            'idx-support_group_member-user_id',
            'support_group_member',
            'user_id'
        );

        $this->addForeignKey(
            'fk-support_group_member-user_id',
            'support_group_member',
            'user_id',
            'user',
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
            'fk-support_group_member-support_group_id',
            'support_group_member'
        );

        $this->dropIndex(
            'idx-support_group_member-support_group_id',
            'support_group_member'
        );


        $this->dropForeignKey(
            'fk-support_group_member-user_id',
            'support_group_member'
        );

        $this->dropIndex(
            'idx-support_group_member-user_id',
            'support_group_member'
        );


        $this->dropTable('support_group_member');
    }
}
