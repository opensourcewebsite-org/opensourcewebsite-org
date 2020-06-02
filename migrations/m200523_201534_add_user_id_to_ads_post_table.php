<?php

use yii\db\Migration;

/**
 * Class m200523_201534_add_user_id_to_ads_post_table
 */
class m200523_201534_add_user_id_to_ads_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post}}', 'user_id', $this->integer()->unsigned()->notNull()->after('id'));

        $this->addForeignKey(
            'fk-ads_post_user_id-bot_user_id',
            '{{%ads_post}}',
            'user_id',
            '{{%bot_user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ads_post_user_id-bot_user_id');

        $this->dropColumn('{{%ads_post}}', 'user_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200523_201534_add_user_id_to_ads_post_table cannot be reverted.\n";

        return false;
    }
    */
}
