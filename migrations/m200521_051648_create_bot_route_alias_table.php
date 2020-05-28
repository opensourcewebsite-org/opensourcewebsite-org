<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_route_alias}}`.
 */
class m200521_051648_create_bot_route_alias_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_route_alias}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'route' => $this->string()->notNull(),
            'text' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_route_alias-chat_id',
            '{{%bot_route_alias}}',
            'chat_id',
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
            'fk-bot_route_alias-group_id',
            '{{%bot_route_alias}}'
        );

        $this->dropTable('{{%bot_route_alias}}');
    }
}
