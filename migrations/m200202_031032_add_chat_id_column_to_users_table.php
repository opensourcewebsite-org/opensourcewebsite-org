<?php

use yii\db\Migration;

/**
 * Handles adding chat_id to table `{{%users}}`.
 */
class m200202_031032_add_chat_id_column_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%groupusers}}', 'chat_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%groupusers}}', 'chat_id');
    }
}
