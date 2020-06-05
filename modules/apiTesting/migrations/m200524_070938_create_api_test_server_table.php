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
        $this->createTable('{{%api_test_server}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer()->unsigned()->comment('Link to project'),
            'protocol' => $this->string(10)->notNull()->comment('Server protocol (http/https)'),
            'domain' => $this->string()->notNull()->comment('Server domain')->unique(),
            'path' => $this->string()->null()->comment('Api path'),
            'txt' => $this->string()->notNull()->comment('TXT record'),
            'status' => $this->boolean()->notNull()->defaultValue(0),
            'txt_checked_at' => $this->integer()->unsigned(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned()
        ]);

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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%api_test_server}}');
    }
}
