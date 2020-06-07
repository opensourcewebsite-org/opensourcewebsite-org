<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_request_headers}}`.
 */
class m200526_043338_create_api_test_request_headers_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_request_headers}}', [
            'id' => $this->primaryKey()->unsigned(),
            'request_id' => $this->integer()->unsigned()->notNull(),
            'key' => $this->string(),
            'value' => $this->string(),
            'description' => $this->string(),
        ]);

        $this->createIndex(
            'idx-api_test_headers-request_id',
            '{{%api_test_request_headers}}',
            'request_id'
        );

        $this->addForeignKey(
            'fk-api_test_headers-request_id',
            '{{%api_test_request_headers}}',
            'request_id',
            '{{%api_test_request}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%api_test_request_headers}}');
    }
}
