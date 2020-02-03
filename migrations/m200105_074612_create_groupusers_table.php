<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%groupusers}}`.
 */
class m200105_074612_create_groupusers_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%groupusers}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(),
            'flag' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%groupusers}}');
    }
}
