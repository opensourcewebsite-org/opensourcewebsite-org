<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_user_state}}`.
 */
class m230421_195234_create_bot_user_state_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_user_state}}', [
            'user_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string(255)->notNull(),
            'value' => $this->json(),
        ]);

        $this->addPrimaryKey(
            'pk-bot_user_state-user_id-name',
            '{{%bot_user_state}}',
            ['user_id', 'name']
        );

        $this->addForeignKey(
            'fk-bot_user_state-user_id',
            '{{%bot_user_state}}',
            'user_id',
            '{{%bot_user}}',
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
            'fk-bot_user_state-user_id',
            '{{%bot_user_state}}'
        );

        $this->dropTable('{{%bot_user_state}}');
    }
}
