<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_request}}`.
 */
class m200526_042917_create_api_test_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_request}}', [
            'id' => $this->primaryKey()->unsigned(),
            'server_id' => $this->integer()->unsigned(),
            'name' => $this->string()->notNull(),
            'method' => $this->string()->notNull(),
            'uri' => $this->string()->notNull(),
            'body' => 'LONGTEXT',
            'correct_response_code' => $this->integer()->notNull()->defaultValue(200),
            'content_type' => $this->string()->notNull()->defaultValue('application/JSON'),
            'expected_response_body' => 'LONGTEXT',
            'updated_at' => $this->integer()->unsigned()->notNull(),
            'updated_by' => $this->integer()->unsigned()->notNull()
        ]);

        $this->createIndex(
            'idx-api_test_request-server_id',
            '{{%api_test_request}}',
            'server_id'
        );

        $this->addForeignKey(
            'fk-api_test_request-server_id',
            '{{%api_test_request}}',
            'server_id',
            '{{%api_test_server}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-user-updated_by',
            '{{%api_test_request}}',
            'updated_by'
        );

        $this->addForeignKey(
            'fk-api_test_request-updated_by',
            '{{%api_test_request}}',
            'updated_by',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%api_test_request}}');
    }
}
