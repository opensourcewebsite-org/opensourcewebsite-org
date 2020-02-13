<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%merge_accounts_request}}`.
 */
class m200208_084413_create_merge_accounts_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%merge_accounts_request}}', [
            'id' => $this->primaryKey()->unsigned(),
            'token' => $this->string()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'user_to_merge_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%merge_accounts_request}}');
    }
}
