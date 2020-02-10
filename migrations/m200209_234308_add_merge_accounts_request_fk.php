<?php

use yii\db\Migration;

/**
 * Class m200209_234308_add_merge_accounts_request_fk
 */
class m200209_234308_add_merge_accounts_request_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-merge_accounts_request-user_to_merge_id',
            'merge_accounts_request',
            'user_to_merge_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-merge_accounts_request-user_id',
            'merge_accounts_request',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-merge_accounts_request-user_to_merge_id',
            'merge_accounts_request'
        );

        $this->dropForeignKey(
            'fk-merge_accounts_request-user_id',
            'merge_accounts_request'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200209_234308_add_merge_accounts_request_fk cannot be reverted.\n";

        return false;
    }
    */
}
