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
            'name' => $this->string()->notNull(),
            'description' => $this->text()->null(),
            'type' => $this->tinyInteger()->notNull(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned()
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
