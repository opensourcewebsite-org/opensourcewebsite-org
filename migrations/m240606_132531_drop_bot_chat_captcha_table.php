<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%bot_chat_captcha}}`.
 */
class m240606_132531_drop_bot_chat_captcha_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-bot_chat_captcha-provider_user_id', '{{%bot_chat_captcha}}');
        $this->dropForeignKey('fk-bot_chat_captcha-chat_id', '{{%bot_chat_captcha}}');
        $this->dropTable('{{%bot_chat_captcha}}');

        $this->dropColumn('{{%bot_user}}', 'captcha_confirmed_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%bot_chat_captcha}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'provider_user_id' => $this->bigInteger()->unsigned()->notNull(),
            'sent_at' => $this->integer()->unsigned()->notNull(),
            'captcha_message_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_captcha-chat_id',
            '{{%bot_chat_captcha}}',
            'chat_id',
            '{{%bot_chat}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_captcha-provider_user_id',
            '{{%bot_chat_captcha}}',
            'provider_user_id',
            '{{%bot_user}}',
            'provider_user_id',
            'CASCADE',
            'CASCADE'
        );

        $this->addColumn('{{%bot_user}}', 'captcha_confirmed_at', $this->integer()->unsigned());
    }
}
