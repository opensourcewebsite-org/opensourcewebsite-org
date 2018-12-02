<?php

use yii\db\Migration;

/**
 * Handles the creation of table `support_group`.
 */
class m181202_095240_create_support_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('support_group', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'language_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string(255)->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
            'updated_by' => $this->integer()->unsigned()->notNull()
        ]);

        /*$this->createIndex(
            'idx-support_group-language_id',
            'support_group',
            'language_id'
        );

        $this->addForeignKey(
            'fk-support_group-language_id',
            'support_group',
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
        /*$this->dropForeignKey(
            'fk-support_group-language_id',
            'support_group'
        );

        $this->dropIndex(
            'idx-support_group-language_id',
            'support_group'
        );*/

        $this->dropTable('support_group');
    }
}
