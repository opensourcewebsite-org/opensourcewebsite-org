<?php

use yii\db\Migration;

/**
 * Class m210525_061010_alter_bot_phrase_table
 */
class m210525_061010_alter_bot_phrase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-bot_phrase-created_by', '{{%bot_phrase}}');
        $this->dropForeignKey('fk-bot_phrase-group_id', '{{%bot_phrase}}');

        $this->dropColumn('{{%bot_phrase}}', 'created_at');

        $this->renameColumn('{{%bot_phrase}}', 'created_by', 'updated_by');

        $this->addForeignKey(
            'fk-bot_phrase-updated_by',
            '{{%bot_phrase}}',
            'updated_by',
            '{{%bot_user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-bot_phrase-chat_id',
            '{{%bot_phrase}}',
            'chat_id',
            '{{%bot_chat}}',
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
        $this->dropForeignKey('fk-bot_phrase-updated_by', '{{%bot_phrase}}');
        $this->dropForeignKey('fk-bot_phrase-chat_id', '{{%bot_phrase}}');

        $this->renameColumn('{{%bot_phrase}}', 'updated_by', 'created_by');

        $this->addColumn('{{%bot_phrase}}', 'created_at', $this->integer()->unsigned()->notNull());

        $this->addForeignKey(
            'fk-bot_phrase-created_by',
            '{{%bot_phrase}}',
            'created_by',
            '{{%bot_user}}',
            'id'
        );

        $this->addForeignKey(
            'fk-bot_phrase-group_id',
            '{{%bot_phrase}}',
            'chat_id',
            '{{%bot_chat}}',
            'id'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210525_061010_alter_bot_phrase_table cannot be reverted.\n";

        return false;
    }
    */
}
