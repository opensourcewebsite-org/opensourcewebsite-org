<?php

use yii\db\Migration;

/**
 * Class m200529_221644_add_currency_id_to_bot_user
 */
class m200529_221644_add_currency_id_to_bot_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_user}}', 'currency_id', $this->integer()->unsigned());

        $this->addForeignKey(
            'fk-bot_user_currency_id-currency_id',
            '{{%bot_user}}',
            'currency_id',
            '{{%currency}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bot_user_currency_id-currency_id');

        $this->dropColumn('{{%bot_user}}', 'currency_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200529_221644_add_currency_id_to_bot_user cannot be reverted.\n";

        return false;
    }
    */
}
