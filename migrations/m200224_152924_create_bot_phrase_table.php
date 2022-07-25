<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_phrase}}`.
 */
class m200224_152924_create_bot_phrase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_phrase}}', [
            'id' => $this->primaryKey()->unsigned(),
            'group_id' => $this->integer()->unsigned()->notNull(),
            'type' => $this->string()->notNull(),
            'text' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_phrase-group_id',
            '{{%bot_phrase}}',
            'group_id',
            '{{%bot_chat}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-bot_phrase-group_id',
            '{{%bot_phrase}}'
        );

        $this->dropTable('{{%bot_phrase}}');
    }
}
