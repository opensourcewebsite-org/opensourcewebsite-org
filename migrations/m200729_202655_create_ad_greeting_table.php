<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_greeting}}`.
 */
class m200729_202655_create_ad_greeting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_greeting}}', [
            'id' => $this->primaryKey()->unsigned(),
            'bot_id' => $this->integer()->unsigned()->notNull(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'provider_user_id' => $this->integer()->unsigned()->notNull(),
            'greeting_text' => $this->string(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_greeting-bot_id',
            '{{%ad_greeting}}',
            'bot_id',
            '{{%bot}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_greeting-chat_id',
            '{{%ad_greeting}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_greeting-provider_user_id',
            '{{%ad_greeting}}',
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
        $this->dropForeignKey('fk-ad_greeting-bot_id', '{{%ad_greeting}}');
        $this->dropForeignKey('fk-ad_greeting-chat_id', '{{%ad_greeting}}');
        $this->dropForeignKey('fk-ad_greeting-provider_user_id', '{{%ad_greeting}}');
        $this->dropTable('{{%ad_greeting}}');
    }
}
