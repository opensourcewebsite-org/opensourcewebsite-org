<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_search}}`.
 */
class m200520_200957_create_ad_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_search}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'section' => $this->tinyInteger()->unsigned()->notNull(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'currency_id' => $this->integer()->unsigned(),
            'max_price' => $this->decimal(15, 2)->unsigned(),
            'pickup_radius' => $this->integer()->unsigned()->notNull(),
            'location_lat' => $this->string()->notNull(),
            'location_lon' => $this->string()->notNull(),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'renewed_at' => $this->integer()->unsigned()->notNull(),
            'processed_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-ad_search_user_id-bot_user_id',
            '{{%ad_search}}',
            'user_id',
            '{{%bot_user}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_search_currency_id-currency_id',
            '{{%ad_search}}',
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
        $this->dropForeignKey('fk-ad_search_user_id-bot_user_id', '{{%ad_search}}');

        $this->dropForeignKey('fk-ad_search_currency_id-currency_id', '{{%ad_search}}');

        $this->dropTable('{{%ad_search}}');
    }
}
