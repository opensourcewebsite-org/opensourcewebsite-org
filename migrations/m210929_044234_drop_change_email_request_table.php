<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%change_email_request}}`.
 */
class m210929_044234_drop_change_email_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-change_email_request-user_id',
            'change_email_request'
        );

        $this->dropTable('{{%change_email_request}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
}
