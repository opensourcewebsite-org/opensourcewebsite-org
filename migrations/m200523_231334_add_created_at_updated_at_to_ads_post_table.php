<?php

use yii\db\Migration;

/**
 * Class m200523_231334_add_created_at_updated_at_to_ads_post_table
 */
class m200523_231334_add_created_at_updated_at_to_ads_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post}}', 'created_at', $this->integer()->unsigned()->notNull());
        $this->addColumn('{{%ads_post}}', 'updated_at', $this->integer()->unsigned()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ads_post}}', 'updated_at');
        $this->dropColumn('{{%ads_post}}', 'created_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200523_231334_add_created_at_updated_at_to_ads_post_table cannot be reverted.\n";

        return false;
    }
    */
}
