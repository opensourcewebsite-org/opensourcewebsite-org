<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_project}}`.
 */
class m200522_044300_create_api_test_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_project}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string()->notNull()->comment('Project name'),
            'description' => $this->text()->null()->comment('Description (optional)'),
            'project_type' => $this->tinyInteger()->notNull()->comment('Project Type'),
            'created_at' => $this->integer()->unsigned()->comment('Created at'),
            'updated_at' => $this->integer()->unsigned()->comment('Updated at')
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-project-user_id',
            '{{%api_test_project}}',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-project-user_id',
            '{{%api_test_project}}',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%api_test_project}}');
    }
}
