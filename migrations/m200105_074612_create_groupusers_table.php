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
        $this->createTable('{{%group_user}}', [
            'id' => $this->primaryKey()->unsigned(),
            'username' => $this->string(),
            'flag' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%group_user}}');
    }
}
