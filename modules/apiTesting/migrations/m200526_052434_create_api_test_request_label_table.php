<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%api_test_request_label}}`.
 */
class m200526_052434_create_api_test_request_label_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%api_test_request_label}}', [
            'label_id' => $this->integer()->unsigned(),
            'request_id' => $this->integer()->unsigned()
        ]);

        $this->addPrimaryKey(
            'pk-test_request_label',
            '{{%api_test_request_label}}',
            ['label_id', 'request_id']
        );

        $this->addForeignKey(
            'fk-test_request_label-label_id',
            '{{%api_test_request_label}}',
            'label_id',
            '{{%api_test_label}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-test_request_label-request_id',
            '{{%api_test_request_label}}',
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
        $this->dropTable('{{%api_test_request_label}}');
    }
}
