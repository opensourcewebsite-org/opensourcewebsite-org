<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_chat_marketplace_link}}`.
 */
class m220906_102400_create_bot_chat_marketplace_link_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_chat_marketplace_link}}', [
            'id' => $this->primaryKey()->unsigned(),
            'member_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string(),
            'url' => $this->string(),
            'updated_by' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_chat_marketplace_link-member_id',
            '{{%bot_chat_marketplace_link}}',
            'member_id',
            '{{%bot_chat_member}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_chat_marketplace_link-updated_by',
            '{{%bot_chat_marketplace_link}}',
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
        $this->dropForeignKey(
            'fk-bot_chat_marketplace_link-member_id',
            '{{%bot_chat_marketplace_link}}'
        );

        $this->dropForeignKey(
            'fk-bot_chat_marketplace_link-updated_by',
            '{{%bot_chat_marketplace_link}}'
        );

        $this->dropTable('{{%bot_chat_marketplace_link}}');
    }
}
