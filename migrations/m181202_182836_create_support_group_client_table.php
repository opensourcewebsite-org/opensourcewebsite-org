<?php

use yii\db\Migration;

/**
 * Handles the creation of table `support_group_client`.
 */
class m181202_182836_create_support_group_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('support_group_client', [
            'id' => $this->primaryKey()->unsigned(),
            'support_group_id' => $this->integer()->unsigned()->notNull(),
            'language_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->createIndex(
            'idx-support_group_client-support_group_id',
            'support_group_client',
            'support_group_id'
        );

        $this->addForeignKey(
            'fk-support_group_client-support_group_id',
            'support_group_client',
            'support_group_id',
            'support_group',
            'id',
            'CASCADE'
        );

        /*$this->createIndex(
            'idx-support_group_client-language_id',
            'support_group_client',
            'language_id'
        );

        $this->addForeignKey(
            'fk-support_group_client-language_id',
            'support_group_client',
            'language_id',
            'language',
            'id',
            'CASCADE'
        );*/
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-support_group_client-support_group_id',
            'support_group_client'
        );

        $this->dropIndex(
            'idx-support_group_client-support_group_id',
            'support_group_client'
        );

        /*$this->dropForeignKey(
            'fk-support_group_client-language_id',
            'support_group_client'
        );

        $this->dropIndex(
            'idx-support_group_client-language_id',
            'support_group_client'
        );*/

        $this->dropTable('support_group_client');
    }
}
