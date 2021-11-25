<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat_marketplace_post}}`.
 */
class m211122_093702_add_columns_to_bot_chat_marketplace_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-bot_chat_marketplace_post-user_id',
            '{{%bot_chat_marketplace_post}}'
        );

        $this->addForeignKey(
            'fk-bot_chat_marketplace_post-user_id',
            '{{%bot_chat_marketplace_post}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addColumn('{{%bot_chat_marketplace_post}}', 'status', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)->after('chat_id'));
        $this->addColumn('{{%bot_chat_marketplace_post}}', 'title', $this->string()->after('status'));

        $this->renameColumn('{{%bot_chat_marketplace_post}}', 'created_at', 'updated_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{%bot_chat_marketplace_post}}', 'updated_at', 'created_at');

        $this->dropColumn('{{%bot_chat_marketplace_post}}', 'status');
        $this->dropColumn('{{%bot_chat_marketplace_post}}', 'title');

        $this->dropForeignKey(
            'fk-bot_chat_marketplace_post-user_id',
            '{{%bot_chat_marketplace_post}}'
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
}
