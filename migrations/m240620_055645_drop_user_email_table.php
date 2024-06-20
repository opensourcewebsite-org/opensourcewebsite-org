<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%user_email}}`.
 */
class m240620_055645_drop_user_email_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%user_email}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%user_email}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'email' => $this->string()->notNull(),
            'confirmed_at' => $this->integer()->unsigned(),
        ]);
    }
}
