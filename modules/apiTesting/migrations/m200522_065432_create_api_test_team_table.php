<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_team}}`.
 */
class m200522_065432_create_api_test_team_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_team}}', [
            'user_id' => $this->integer()->unsigned(),
            'project_id' => $this->integer()->unsigned(),
            'invited_at' => $this->integer()->unsigned(),
            'invited_by' => $this->integer()->unsigned(),
            'status' => $this->tinyInteger()->unsigned()->notNull()
        ]);

        $this->addPrimaryKey('pk-api_test_team', '{{%api_test_team}}', [
            'user_id',
            'project_id'
        ]);

        $this->createIndex(
            'idx-api_test_team-invited_by',
            '{{%api_test_team}}',
            'invited_by'
        );

        $this->addForeignKey(
            'fk-api_test_team-invited_by',
            '{{%api_test_team}}',
            'invited_by',
            'user',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-api_test_team-user_id',
            '{{%api_test_team}}',
            'user_id'
        );

        $this->addForeignKey(
            'fk-api_test_team-user_id',
            '{{%api_test_team}}',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-api_test_team-project_id',
            '{{%api_test_team}}',
            'project_id'
        );

        $this->addForeignKey(
            'fk-project-project_id',
            '{{%api_test_team}}',
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
        $this->dropTable('{{%api_test_team}}');
    }
}
