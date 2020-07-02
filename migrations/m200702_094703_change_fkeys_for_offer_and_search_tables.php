<?php

use yii\db\Migration;

/**
 * Class m200702_094703_change_fkeys_for_offer_and_search_tables
 */
class m200702_094703_change_fkeys_for_offer_and_search_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-ad_offer_user_id-bot_user_id', '{{%ad_offer}}');
        $this->dropForeignKey('fk-ad_search_user_id-bot_user_id', '{{%ad_search}}');
        $this->addForeignKey(
            'fk-ad_offer_user_id-user_id',
            '{{%ad_offer}}',
            'user_id',
            '{{%user}}',
            'id'
        );
        $this->addForeignKey(
            'fk-ad_search_user_id-user_id',
            '{{%ad_search}}',
            'user_id',
            '{{%user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200702_094703_change_fkeys_for_offer_and_search_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200702_094703_change_fkeys_for_offer_and_search_tables cannot be reverted.\n";

        return false;
    }
    */
}
