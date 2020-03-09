<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_phrase}}`.
 */
class m200302_193727_add_created_at_created_by_columns_to_bot_phrase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_phrase}}', 'created_at', $this->integer()->unsigned()->notNull());
        $this->addColumn('{{%bot_phrase}}', 'created_by', $this->integer()->unsigned()->notNull());

        $this->addForeignKey(
            'fk-bot_phrase-created_by',
            '{{%bot_phrase}}',
            'created_by',
            '{{%bot_user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bot_phrase-created_by');

        $this->dropColumn('{{%bot_phrase}}', 'created_by');
        $this->dropColumn('{{%bot_phrase}}', 'created_at');
    }
}
