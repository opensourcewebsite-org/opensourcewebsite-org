<?php

use yii\db\Migration;

/**
 * Class m181025_173637_add_referral_to_user_table
 */
class m181025_173637_add_referral_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'referrer_id', $this->integer()->unsigned()->after('status'));

        $this->createIndex(
            '{{%idx-user-referrer_id}}',
            '{{%user}}',
            'referrer_id'
        );

        $this->addForeignKey(
            '{{%fk-user-referrer_id}}',
            '{{%user}}',
            'referrer_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-user-referrer_id}}', '{{%user}}');
        $this->dropColumn('{{%user}}', 'referrer_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181025_173637_add_referral_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
