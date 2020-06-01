<?php

use yii\db\Migration;

/**
 * Class m200424_023849_create_table_bot_ads_post
 */
class m200424_023849_create_table_bot_ads_post extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ads_post}}', [
            'id' => $this->primaryKey()->unsigned(),
            'title' => $this->string()->notNull(),
            'description' => $this->string()->notNull(),
            'price' => $this->string()->notNull(),
            'delivery_km' => $this->integer()->unsigned(),
            'location_lat' => $this->string(255)->notNull(),
            'location_lon' => $this->string(255)->notNull(),
            'category_id' => $this->integer()->unsigned()->notNull(),
            'status' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ads_post}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200424_023849_create_table_bot_ads_post cannot be reverted.\n";

        return false;
    }
    */
}
