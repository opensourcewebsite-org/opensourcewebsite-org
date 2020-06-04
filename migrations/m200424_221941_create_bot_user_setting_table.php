<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_user_setting}}`.
 */
class m200424_221941_create_bot_user_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_user_setting}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'setting' => $this->string()->notNull(),
            'value' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_user_setting_user_id-bot_user_id',
            '{{%bot_user_setting}}',
            'user_id',
            '{{%bot_user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bot_user_setting_user_id-bot_user_id');

        $this->dropTable('{{%bot_user_setting}}');
    }
}
