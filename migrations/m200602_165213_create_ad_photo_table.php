<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_photo}}`.
 */
class m200602_165213_create_ad_photo_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_photo}}', [
            'id' => $this->primaryKey()->unsigned()->notNull(),
            'ad_order_id' => $this->integer()->unsigned()->notNull(),
            'file_id' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_photo_ad_order_id-ad_order_id',
            '{{%ad_photo}}',
            'ad_order_id',
            '{{%ad_order}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_photo_ad_order_id-ad_order_id');

        $this->dropTable('{{%ad_photo}}');
    }
}
