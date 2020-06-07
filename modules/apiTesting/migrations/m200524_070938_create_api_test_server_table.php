<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_server}}`.
 */
class m200524_070938_create_api_test_server_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_domain}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer()->unsigned(),
            'domain' => $this->string()->notNull(),
            'status' => $this->boolean()->notNull()->defaultValue(0),
            'txt' => $this->string()->notNull(),
            'txt_checked_at' => $this->integer()->unsigned(),
        ]);

        $this->createTable('{{%api_test_server}}', [
            'id' => $this->primaryKey()->unsigned(),
            'domain_id' => $this->integer()->notNull()->unsigned(),
            'project_id' => $this->integer()->unsigned(),
            'protocol' => $this->tinyInteger()->notNull(),
            'path' => $this->string()->null(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned()
        ]);

        $this->createIndex(
            'idx-api_test_domain-project_id',
            '{{%api_test_domain}}',
            'project_id'
        );

        $this->addForeignKey(
            'fk-api_test_domain-project_id',
            '{{%api_test_domain}}',
            'project_id',
            '{{%api_test_project}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-api_test_server-project_id',
            '{{%api_test_server}}',
            'project_id'
        );

        $this->addForeignKey(
            'fk-api_test_server-project_id',
            '{{%api_test_server}}',
            'project_id',
            '{{%api_test_project}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-api_test_server-domain_id',
            '{{%api_test_server}}',
            'domain_id'
        );

        $this->addForeignKey(
            'fk-api_test_server-domain_id',
            '{{%api_test_server}}',
            'domain_id',
            '{{%api_test_domain}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%api_test_server}}');
        $this->dropTable('{{%api_test_domain}}');
    }
}
