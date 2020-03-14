<?php

use yii\db\Migration;

/**
 * Class m200301_174223_bot_admin_table
 */
class m200301_174223_bot_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_admin}}', [
            'id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'telegram_user_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-bot_admin-chat_id',
            '{{%bot_admin}}',
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
        $this->dropForeignKey('fk-bot_admin-chat_id');

        $this->dropTable('{{%bot_admin}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200301_174223_bot_admin_table cannot be reverted.\n";

        return false;
    }
    */
}
