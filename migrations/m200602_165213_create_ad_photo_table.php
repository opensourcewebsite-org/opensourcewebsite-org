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
            'ad_offer_id' => $this->integer()->unsigned()->notNull(),
            'file_id' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_photo_ad_offer_id-ad_offer_id',
            '{{%ad_photo}}',
            'ad_offer_id',
            '{{%ad_offer}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_photo_ad_offer_id-ad_offer_id', '{{%ad_photo}}');

        $this->dropTable('{{%ad_photo}}');
    }
}
