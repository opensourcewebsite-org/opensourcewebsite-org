<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat}}`.
 */
class m220818_043423_add_currency_id_column_to_bot_chat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat}}', 'currency_id', $this->integer()->unsigned());

        $this->addForeignKey(
            'fk-bot_chat_currency_id-currency_id',
            '{{%bot_chat}}',
            'currency_id',
            'currency',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_chat_currency_id-currency_id',
            '{{%bot_chat}}'
        );

        $this->dropColumn('{{%bot_chat}}', 'currency_id');
    }
}
