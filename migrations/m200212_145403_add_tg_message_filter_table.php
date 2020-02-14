<?php

use yii\db\Migration;

/**
 * Class m200212_145403_add_tg_message_filter_table
 */
class m200212_145403_add_tg_message_filter_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_message_filter}}', [
            'id' => $this->primaryKey()->unsigned(),
            'provider_user_id' => $this->integer()->unsigned()->notNull(),
            'filter_word' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200212_145403_add_tg_message_filter_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200212_145403_add_tg_message_filter_table cannot be reverted.\n";

        return false;
    }
    */
}
