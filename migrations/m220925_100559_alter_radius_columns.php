<?php

use yii\db\Migration;

/**
 * Class m220925_100559_alter_radius_columns
 */
class m220925_100559_alter_radius_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%ad_offer}}', 'delivery_radius', $this->integer()->unsigned()->defaultValue(0)->notNull());
        $this->alterColumn('{{%ad_search}}', 'pickup_radius', $this->integer()->unsigned()->defaultValue(0)->notNull());

        $this->update('{{%currency_exchange_order}}', ['selling_delivery_radius' => 0], ['selling_delivery_radius' => null]);
        $this->update('{{%currency_exchange_order}}', ['buying_delivery_radius' => 0], ['buying_delivery_radius' => null]);

        $this->alterColumn('{{%currency_exchange_order}}', 'selling_delivery_radius', $this->integer()->unsigned()->defaultValue(0)->notNull());
        $this->alterColumn('{{%currency_exchange_order}}', 'buying_delivery_radius', $this->integer()->unsigned()->defaultValue(0)->notNull());

        $this->update('{{%resume}}', ['search_radius' => 0], ['search_radius' => null]);

        $this->alterColumn('{{%resume}}', 'search_radius', $this->integer()->unsigned()->defaultValue(0)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%ad_offer}}', 'delivery_radius', $this->integer()->unsigned()->notNull());
        $this->alterColumn('{{%ad_search}}', 'pickup_radius', $this->integer()->unsigned()->notNull());

        $this->alterColumn('{{%currency_exchange_order}}', 'selling_delivery_radius', $this->integer()->unsigned());
        $this->alterColumn('{{%currency_exchange_order}}', 'buying_delivery_radius', $this->integer()->unsigned());

        $this->alterColumn('{{%resume}}', 'search_radius', $this->integer()->unsigned());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220925_100559_alter_radius_columns cannot be reverted.\n";

        return false;
    }
    */
}
