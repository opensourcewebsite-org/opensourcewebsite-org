<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%bot}}`.
 */
class m220921_125731_drop_bot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-bot_chat-bot_id',
            '{{%bot_chat}}'
        );

        $this->dropColumn('{{%bot_chat}}', 'bot_id');

        $this->dropTable('{{%bot}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%bot}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'token' => $this->string(255)->notNull(),
            'status' => $this->smallInteger()->unsigned()->defaultValue(0)->notNull(),
        ]);

        $this->addColumn('{{%bot_chat}}', 'bot_id', $this->integer()->unsigned()->after('created_at'));

        $this->addForeignKey(
            'fk-bot_chat-bot_id',
            '{{%bot_chat}}',
            'bot_id',
            '{{%bot}}',
            'id'
        );
    }
}
