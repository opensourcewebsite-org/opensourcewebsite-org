<?php

use yii\db\Migration;

/**
 * Class m211024_100028_bot_chat_marketplace_post
 */
class m211024_100028_bot_chat_marketplace_post extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_marketplace_post}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'text' => $this->text()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'sent_at' => $this->integer()->unsigned(),
            'provider_message_id' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_marketplace_post-chat_id',
            '{{%bot_chat_marketplace_post}}',
            'chat_id',
            '{{%bot_chat}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_marketplace_post-user_id',
            '{{%bot_chat_marketplace_post}}',
            'user_id',
            '{{%bot_user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_chat_marketplace_post-chat_id',
            '{{%bot_chat_marketplace_post}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_marketplace_post-user_id',
            '{{%bot_chat_marketplace_post}}'
        );

        $this->dropTable('{{%bot_chat_marketplace_post}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211024_100028_bot_chat_marketplace_post cannot be reverted.\n";

        return false;
    }
    */
}
