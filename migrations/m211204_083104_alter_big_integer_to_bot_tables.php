<?php

use yii\db\Migration;

/**
 * Class m211204_083104_alter_big_integer_to_bot_tables
 */
class m211204_083104_alter_big_integer_to_bot_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-bot_chat_captcha-provider_user_id', '{{%bot_chat_captcha}}');
        $this->dropForeignKey('fk-bot_chat_captcha-chat_id', '{{%bot_chat_captcha}}');
        $this->dropForeignKey('fk-bot_chat_greeting-provider_user_id', '{{%bot_chat_greeting}}');
        $this->dropForeignKey('fk-bot_chat_greeting-chat_id', '{{%bot_chat_greeting}}');

        $this->alterColumn('{{%bot_user}}', 'provider_user_id', $this->bigInteger()->unsigned()->notNull());
        $this->alterColumn('{{%bot_chat_captcha}}', 'provider_user_id', $this->bigInteger()->unsigned()->notNull());
        $this->alterColumn('{{%bot_chat_greeting}}', 'provider_user_id', $this->bigInteger()->unsigned()->notNull());

        $this->addForeignKey(
            'fk-bot_chat_greeting-chat_id',
            '{{%bot_chat_greeting}}',
            'chat_id',
            '{{%bot_chat}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_greeting-provider_user_id',
            '{{%bot_chat_greeting}}',
            'provider_user_id',
            '{{%bot_user}}',
            'provider_user_id',
            'CASCADE',
            'CASCADE'
        );

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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bot_chat_captcha-provider_user_id', '{{%bot_chat_captcha}}');
        $this->dropForeignKey('fk-bot_chat_captcha-chat_id', '{{%bot_chat_captcha}}');
        $this->dropForeignKey('fk-bot_chat_greeting-provider_user_id', '{{%bot_chat_greeting}}');
        $this->dropForeignKey('fk-bot_chat_greeting-chat_id', '{{%bot_chat_greeting}}');

        $this->alterColumn('{{%bot_user}}', 'provider_user_id', $this->integer()->unsigned()->notNull());
        $this->alterColumn('{{%bot_chat_captcha}}', 'provider_user_id', $this->integer()->unsigned()->notNull());
        $this->alterColumn('{{%bot_chat_greeting}}', 'provider_user_id', $this->integer()->unsigned()->notNull());

        $this->addForeignKey(
            'fk-bot_chat_greeting-chat_id',
            '{{%bot_chat_greeting}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_greeting-provider_user_id',
            '{{%bot_chat_greeting}}',
            'provider_user_id',
            '{{%bot_user}}',
            'provider_user_id'
        );

        $this->addForeignKey(
            'fk-bot_chat_captcha-chat_id',
            '{{%bot_chat_captcha}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_captcha-provider_user_id',
            '{{%bot_chat_captcha}}',
            'provider_user_id',
            '{{%bot_user}}',
            'provider_user_id'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211204_083104_alter_big_integer_to_bot_tables cannot be reverted.\n";

        return false;
    }
    */
}
