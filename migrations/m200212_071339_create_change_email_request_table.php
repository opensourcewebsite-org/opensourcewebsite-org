<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%change_email_request}}`.
 */
class m200212_071339_create_change_email_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%change_email_request}}', [
            'id' => $this->primaryKey()->unsigned(),
            'token' => $this->string()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'email' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignkey(
            'fk-change_email_request-user_id',
            'change_email_request',
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
            'fk-change_email_request-user_id',
            'change_email_request',
        );
        $this->dropTable('{{%change_email_request}}');
    }
}
