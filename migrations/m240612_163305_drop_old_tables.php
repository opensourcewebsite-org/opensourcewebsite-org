<?php

use yii\db\Migration;

/**
 * Class m240612_163305_drop_old_tables
 */
class m240612_163305_drop_old_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('css_moqup_id_fk', 'css');
        $this->dropTable('css');

        $this->dropForeignKey(
            'fk-user_moqup_follow-moqup_id',
            'user_moqup_follow'
        );

        $this->dropIndex(
            'idx-user_moqup_follow-moqup_id',
            'user_moqup_follow'
        );

        $this->dropForeignKey(
            'fk-user_moqup_follow-user_id',
            'user_moqup_follow'
        );

        $this->dropIndex(
            'idx-user_moqup_follow-user_id',
            'user_moqup_follow'
        );

        $this->dropTable('user_moqup_follow');

        $this->dropForeignKey('fk-moqup_comment-moqup_id', 'moqup_comment');
        $this->dropForeignKey('fk-moqup_comment-user_id', 'moqup_comment');
        $this->dropIndex('idx-moqup_comment-parent_id', 'moqup_comment');

        $this->dropTable('moqup_comment');

        $this->dropForeignKey(
            'fk-moqup-forked_of',
            'moqup'
        );

        $this->dropTable('moqup');

        $this->dropForeignKey(
            'fk-user_issue_vote-user_id',
            'user_issue_vote'
        );

        $this->dropIndex(
            'idx-user_issue_vote-user_id',
            'user_issue_vote'
        );

        $this->dropForeignKey(
            'fk-user_issue_vote-issue_id',
            'user_issue_vote'
        );

        $this->dropIndex(
            'idx-user_issue_vote-issue_id',
            'user_issue_vote'
        );

        $this->dropTable('user_issue_vote');

        $this->dropForeignKey('fk-issue_comment-issue_id', 'issue_comment');
        $this->dropForeignKey('fk-issue_comment-user_id', 'issue_comment');
        $this->dropIndex('idx-issue_comment-parent_id', 'issue_comment');

        $this->dropTable('issue_comment');

        $this->dropForeignKey(
            'fk-issue-user_id',
            'issue'
        );

        $this->dropIndex(
            'idx-issue-user_id',
            'issue'
        );

        $this->dropTable('issue');

        $this->dropTable('{{%support_group_exchange_rate_command}}');

        $this->dropTable('{{%support_group_exchange_rate}}');

        $this->dropForeignKey(
            'fk-support_group_outside_message-support_group_bot_client_id',
            'support_group_outside_message'
        );

        $this->dropIndex(
            'idx-support_group_outside_message-support_group_bot_client_id',
            'support_group_outside_message'
        );

        $this->renameColumn('support_group_outside_message', 'support_group_bot_client_id', 'support_group_client_id');

        $this->createIndex(
            'idx-support_group_outside_message-support_group_client_id',
            'support_group_outside_message',
            'support_group_client_id'
        );

        $this->addForeignKey(
            'fk-support_group_outside_message-support_group_client_id',
            'support_group_outside_message',
            'support_group_client_id',
            'support_group_client',
            'id',
            'CASCADE'
        );

        $this->dropForeignKey(
            'fk-support_group_inside_message-support_group_bot_client_id',
            'support_group_inside_message'
        );

        $this->dropIndex(
            'idx-support_group_inside_message-support_group_bot_client_id',
            'support_group_inside_message'
        );

        $this->renameColumn('support_group_inside_message', 'support_group_bot_client_id', 'support_group_client_id');

        $this->createIndex(
            'idx-support_group_inside_message-support_group_client_id',
            'support_group_inside_message',
            'support_group_client_id'
        );

        $this->addForeignKey(
            'fk-support_group_inside_message-support_group_client_id',
            'support_group_inside_message',
            'support_group_client_id',
            'support_group_client',
            'id',
            'CASCADE'
        );

        $this->dropIndex('idx-support_group_bot_client-support_group_user_id', 'support_group_bot_client');

        $this->dropForeignKey(
            'fk-support_group_bot_client-support_group_bot_id',
            'support_group_bot_client'
        );

        $this->dropIndex(
            'idx-support_group_bot_client-support_group_bot_id',
            'support_group_bot_client'
        );

        $this->dropForeignKey(
            'fk-support_group_bot_client-support_group_client_id',
            'support_group_bot_client'
        );

        $this->dropIndex(
            'idx-support_group_bot_client-support_group_client_id',
            'support_group_bot_client'
        );

        $this->renameTable('support_group_bot_client', 'support_group_client_bot');

        # create new
        $this->createIndex(
            'idx-support_group_client_bot-support_group_bot_id',
            'support_group_client_bot',
            'support_group_bot_id'
        );

        $this->addForeignKey(
            'fk-support_group_client_bot-support_group_bot_id',
            'support_group_client_bot',
            'support_group_bot_id',
            'support_group_bot',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-support_group_client_bot-support_group_client_id',
            'support_group_client_bot',
            'support_group_client_id'
        );

        $this->addForeignKey(
            'fk-support_group_client_bot-support_group_client_id',
            'support_group_client_bot',
            'support_group_client_id',
            'support_group_client',
            'id',
            'CASCADE'
        );

        $this->dropForeignKey(
            'fk-support_group_outside_message-support_group_client_id',
            'support_group_outside_message'
        );

        $this->dropIndex(
            'idx-support_group_outside_message-support_group_client_id',
            'support_group_outside_message'
        );


        $this->dropForeignKey(
            'fk-support_group_outside_message-support_group_bot_id',
            'support_group_outside_message'
        );

        $this->dropIndex(
            'idx-support_group_outside_message-support_group_bot_id',
            'support_group_outside_message'
        );

        $this->dropTable('support_group_outside_message');

        $this->dropForeignKey(
            'fk-support_group_language-language_code',
            'support_group_language'
        );

        $this->dropIndex(
            'idx-support_group_language-language_code',
            'support_group_language'
        );

        $this->dropTable('support_group_language');

        $this->dropForeignKey(
            'fk-support_group_client_bot-support_group_bot_id',
            'support_group_client_bot'
        );

        $this->dropIndex(
            'idx-support_group_client_bot-support_group_bot_id',
            'support_group_client_bot'
        );


        $this->dropForeignKey(
            'fk-support_group_client_bot-support_group_client_id',
            'support_group_client_bot'
        );

        $this->dropIndex(
            'idx-support_group_client_bot-support_group_client_id',
            'support_group_client_bot'
        );

        $this->dropTable('support_group_client_bot');

        $this->dropForeignKey(
            'fk-support_group_inside_message-support_group_client_id',
            'support_group_inside_message'
        );

        $this->dropIndex(
            'idx-support_group_inside_message-support_group_client_id',
            'support_group_inside_message'
        );


        $this->dropForeignKey(
            'fk-support_group_inside_message-support_group_bot_id',
            'support_group_inside_message'
        );

        $this->dropIndex(
            'idx-support_group_inside_message-support_group_bot_id',
            'support_group_inside_message'
        );

        $this->dropTable('support_group_inside_message');

        $this->dropForeignKey(
            'fk-support_group_client-support_group_id',
            'support_group_client'
        );

        $this->dropIndex(
            'idx-support_group_client-support_group_id',
            'support_group_client'
        );

        $this->dropForeignKey(
            'fk-support_group_client-language_code',
            'support_group_client'
        );

        $this->dropIndex(
            'idx-support_group_client-language_code',
            'support_group_client'
        );

        $this->dropTable('support_group_client');

        $this->dropForeignKey(
            'fk-support_group_command_text-support_group_command_id',
            'support_group_command_text'
        );

        $this->dropIndex(
            'idx-support_group_command_text-support_group_command_id',
            'support_group_command_text'
        );

        $this->dropForeignKey(
            'fk-support_group_command_text-language_code',
            'support_group_command_text'
        );

        $this->dropIndex(
            'idx-support_group_command_text-language_code',
            'support_group_command_text'
        );

        $this->dropTable('support_group_command_text');

        $this->dropForeignKey(
            'fk-support_group_command-support_group_id',
            'support_group_command'
        );

        $this->dropIndex(
            'idx-support_group_command-support_group_id',
            'support_group_command'
        );

        $this->dropTable('support_group_command');

        $this->dropForeignKey(
            'fk-support_group_bot-support_group_id',
            'support_group_bot'
        );

        $this->dropIndex(
            'idx-support_group_bot-support_group_id',
            'support_group_bot'
        );

        $this->dropTable('support_group_bot');

        $this->dropForeignKey(
            'fk-support_group_member-support_group_id',
            'support_group_member'
        );

        $this->dropIndex(
            'idx-support_group_member-support_group_id',
            'support_group_member'
        );


        $this->dropForeignKey(
            'fk-support_group_member-user_id',
            'support_group_member'
        );

        $this->dropIndex(
            'idx-support_group_member-user_id',
            'support_group_member'
        );

        $this->dropTable('support_group_member');

        $this->dropTable('support_group');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240612_163305_drop_old_tables cannot be reverted.\n";

        return false;
    }
}
