<?php

use yii\db\Migration;

/**
 * Class m200424_023849_create_ad_order_table
 */
class m200424_023849_create_ad_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_order}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'category_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string()->notNull(),
            'description' => $this->text()->notNull(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'price' => $this->decimal(15, 2)->unsigned()->notNull(),
            'delivery_radius' => $this->integer()->unsigned()->notNull(),
            'location_lat' => $this->string()->notNull(),
            'location_lon' => $this->string()->notNull(),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'renewed_at' => $this->integer()->unsigned()->notNull(),
            'edited_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-ad_order_user_id-bot_user_id',
            '{{%ad_order}}',
            'user_id',
            '{{%bot_user}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_order_currency_id-currency_id',
            '{{%ad_order}}',
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
        $this->dropForeignKey('fk-ad_order_currency_id-currency_id');

        $this->dropForeignKey('fk-ad_order_user_id-bot_user_id');

        $this->dropTable('{{%ad_order}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200424_023849_create_ad_order_table cannot be reverted.\n";

        return false;
    }
    */
}
