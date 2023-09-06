<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat}}`.
 */
class m230829_161343_add_language_id_column_to_bot_chat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat}}', 'language_id', $this->integer()->unsigned()->after('currency_id'));

         $this->addForeignKey(
             'fk-bot_chat-language_id',
             '{{%bot_chat}}',
             'language_id',
             '{{%language}}',
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
            'fk-bot_chat-language_id',
            '{{%bot_chat}}'
        );

        $this->dropColumn('{{%bot_chat}}', 'language_id');
    }
}
