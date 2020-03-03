<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_setting}}`.
 */
class m200303_032753_create_bot_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_setting}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'setting' => $this->string(),
            'value' => $this->string(),
        ]);

        $this->addForeignKey(
            'fk-bot_setting-chat_id',
            '{{%bot_setting}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-vot_setting-chat_id');
        
        $this->dropTable('{{%bot_setting}}');
    }
}
