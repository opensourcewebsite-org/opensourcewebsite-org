<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_tip_message}}`.
 */
class m230116_182639_create_bot_chat_tip_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_tip_message}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'from_user_id' => $this->integer()->unsigned()->notNull(),
            'to_user_id' => $this->integer()->unsigned()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'message_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_tip_message-chat_id',
            '{{%bot_chat_tip_message}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_chat_tip_message-currency_id',
            '{{%bot_chat_tip_message}}',
            'currency_id',
            '{{%currency}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_tip_message-from_user_id',
            '{{%bot_chat_tip_message}}',
            'from_user_id',
            '{{%bot_user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_tip_message-to_user_id',
            '{{%bot_chat_tip_message}}',
            'to_user_id',
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
            'fk-bot_chat_tip_message-chat_id',
            '{{%bot_chat_tip_message}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_tip_message-currency_id',
            '{{%bot_chat_tip_message}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_tip_message-from_user_id',
            '{{%bot_chat_tip_message}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_tip_message-to_user_id',
            '{{%bot_chat_tip_message}}'
        );

        $this->dropTable('{{%bot_chat_tip_message}}');
    }
}
