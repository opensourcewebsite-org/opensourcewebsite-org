<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Handles adding columns to table `{{%bot_client}}`.
 */
class m200212_063716_add_bot_id_column_to_bot_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('bot_client', 'bot_id', $this->integer()->unsigned());

        $this->addForeignKey(
            'fk-bot_client-bot_id',
            'bot_client',
            'bot_id',
            'bot',
            'id',
            'CASCADE'
        );

        $botId = (new Query())->select('id')->from('bot')->one();

        $this->update(
            'bot_client',
            [ 'bot_id' => $botId ],
            [ 'bot_id' => null ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_client-bot_id',
            'bot_client'
        );

        $this->dropColumn('bot_client', 'bot_id');
    }
}
