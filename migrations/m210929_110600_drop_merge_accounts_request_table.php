<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%merge_accounts_request}}`.
 */
class m210929_110600_drop_merge_accounts_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-merge_accounts_request-user_to_merge_id',
            'merge_accounts_request'
        );

        $this->dropForeignKey(
            'fk-merge_accounts_request-user_id',
            'merge_accounts_request'
        );

        $this->dropTable('{{%merge_accounts_request}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%merge_accounts_request}}', [
            'id' => $this->primaryKey()->unsigned(),
            'token' => $this->string()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'user_to_merge_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-merge_accounts_request-user_to_merge_id',
            'merge_accounts_request',
            'user_to_merge_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-merge_accounts_request-user_id',
            'merge_accounts_request',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }
}
