<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_faq_question}}`.
 */
class m210524_052216_create_bot_chat_faq_question_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_faq_question}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'text' => $this->string()->notNull(),
            'answer' => $this->text(),
            'updated_by' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_faq_question-chat_id',
            '{{%bot_chat_faq_question}}',
            'chat_id',
            '{{%bot_chat}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_faq_question-updated_by',
            '{{%bot_chat_faq_question}}',
            'updated_by',
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
        $this->dropForeignKey('fk-bot_chat_faq_question-updated_by', '{{%bot_chat_faq_question}}');
        $this->dropForeignKey('fk-bot_chat_faq_question-chat_id', '{{%bot_chat_faq_question}}');

        $this->dropTable('{{%bot_chat_faq_question}}');
    }
}
