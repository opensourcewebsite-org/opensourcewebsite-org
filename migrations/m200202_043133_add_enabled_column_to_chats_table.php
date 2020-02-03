<?php

use yii\db\Migration;

/**
 * Handles adding enabled to table `{{%chats}}`.
 */
class m200202_043133_add_enabled_column_to_chats_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%group_chats}}', 'enabled', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%group_chats}}', 'enabled');
    }
}
