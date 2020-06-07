<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_label}}`.
 */
class m200526_042909_create_api_test_label_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_label}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        $this->createIndex(
            'idx-api_test_label-project_id',
            '{{%api_test_label}}',
            'project_id'
        );

        $this->addForeignKey(
            'fk-api_test_label-server_id',
            '{{%api_test_label}}',
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
        $this->dropTable('{{%api_test_label}}');
    }
}
