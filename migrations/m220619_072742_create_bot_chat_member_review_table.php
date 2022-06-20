<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_member_review}}`.
 */
class m220619_072742_create_bot_chat_member_review_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_member_review}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'member_id' => $this->integer()->unsigned()->notNull(),
            'text' => $this->text(),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_member_review-user_id',
            '{{%bot_chat_member_review}}',
            'user_id',
            '{{%bot_user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_member_review-member_id',
            '{{%bot_chat_member_review}}',
            'member_id',
            '{{%bot_chat_member}}',
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
        $this->dropForeignKey('fk-bot_chat_member_review-user_id', '{{%bot_chat_member_review}}');
        $this->dropForeignKey('fk-bot_chat_member_review-member_id', '{{%bot_chat_member_review}}');

        $this->dropTable('{{%bot_chat_member_review}}');
    }
}
