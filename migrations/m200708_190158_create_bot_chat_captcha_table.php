<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_captcha}}`.
 */
class m200708_190158_create_bot_chat_captcha_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_captcha}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'provider_user_id' => $this->integer()->unsigned()->notNull(),
            'sent_at' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_captcha-chat_id',
            '{{%bot_chat_captcha}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->createIndex(
            'idx-provider_user_id',
            '{{%bot_user}}',
            'provider_user_id',
            true);

        $this->addForeignKey(
            'fk-bot_chat_captcha-provider_user_id',
            '{{%bot_chat_captcha}}',
            'provider_user_id',
            '{{%bot_user}}',
            'provider_user_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bot_chat_captcha-provider_user_id', '{{%bot_chat_captcha}}');
        $this->dropIndex('idx-provider_user_id', '{{%bot_user}}');
        $this->dropForeignKey('fk-bot_chat_captcha-chat_id', '{{%bot_chat_captcha}}');
        $this->dropTable('{{%bot_chat_captcha}}');
    }
}
