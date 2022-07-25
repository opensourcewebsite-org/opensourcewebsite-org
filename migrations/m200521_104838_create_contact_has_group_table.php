<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%contact_has_group}}`.
 */
class m200521_104838_create_contact_has_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%contact_has_group}}', [
            'id' => $this->primaryKey()->unsigned(),
            'contact_group_id' => $this->integer()->unsigned()->notNull(),
            'contact_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->createIndex(
            'idx-contact_has_group-contact_group_id',
            'contact_has_group',
            'contact_group_id'
        );

        $this->addForeignKey(
            'fk-contact_has_group-contact_group_id',
            'contact_has_group',
            'contact_group_id',
            'contact_group',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-contact_has_group-contact_id',
            'contact_has_group',
            'contact_id'
        );

        $this->addForeignKey(
            'fk-contact_has_group-contact_id',
            'contact_has_group',
            'contact_id',
            'contact',
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
            'fk-contact_has_group-contact_group_id',
            'contact_has_group'
        );

        $this->dropIndex(
            'idx-contact_has_group-contact_group_id',
            'contact_has_group'
        );


        $this->dropForeignKey(
            'fk-contact_has_group-contact_id',
            'contact_has_group'
        );

        $this->dropIndex(
            'idx-contact_has_group-contact_id',
            'contact_has_group'
        );

        $this->dropTable('{{%contact_has_group}}');
    }
}
