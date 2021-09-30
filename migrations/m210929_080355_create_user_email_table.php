<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_email}}`.
 */
class m210929_080355_create_user_email_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_email}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'email' => $this->string()->notNull(),
            'confirmed_at' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_email}}');
    }
}
