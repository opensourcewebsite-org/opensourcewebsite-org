<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%contact_group}}`.
 */
class m200521_104820_create_contact_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%contact_group}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
        ]);
        $this->createIndex('uniq-idx-name-user-id', 'contact_group', ['name', 'user_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%contact_group}}');
    }
}
