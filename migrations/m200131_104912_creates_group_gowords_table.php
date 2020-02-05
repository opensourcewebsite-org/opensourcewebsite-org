<?php

use yii\db\Migration;

/**
 * Class m200131_104912_creates_group_gowords_table
 */
class m200131_104912_creates_group_gowords_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_goword}}', [
            '_id' => $this->primaryKey()->unsigned(),
            'chat_id' => $this->integer()->unsigned()->notNull(),
            'text' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-goword-chat_id-to-chat',
            '{{%group_goword}}',
            'chat_id',
            '{{%group_chat}}',
            '_id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropForeignKey('fk-goword-chat_id-to-chat', '{{%group_goword}}');
        
        $this->dropTable('{{%group_goword}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200131_104912_creates_group_gowords_table cannot be reverted.\n";

        return false;
    }
    */
}
