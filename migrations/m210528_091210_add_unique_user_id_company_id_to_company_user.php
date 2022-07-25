<?php

use yii\db\Migration;

/**
 * Class m210528_091210_add_unique_user_id_company_id_to_company_user
 */
class m210528_091210_add_unique_user_id_company_id_to_company_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('user_id_company_id', '{{%company_user}}', ['user_id', 'company_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('user_id_company_id', '{{%company_user}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210528_091210_add_unique_user_id_company_id_to_company_user cannot be reverted.\n";

        return false;
    }
    */
}
