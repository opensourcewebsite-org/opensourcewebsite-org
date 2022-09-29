<?php

use yii\db\Migration;

/**
 * Class m220929_052007_rename_ad_photo_table
 */
class m220929_052007_rename_ad_photo_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-ad_photo_ad_offer_id-ad_offer_id', '{{%ad_photo}}');

        $this->renameTable('{{%ad_photo}}', '{{%ad_offer_photo}}');

        $this->addForeignKey(
            'fk-ad_offer_photo-ad_offer_id',
            '{{%ad_offer_photo}}',
            'ad_offer_id',
            '{{%ad_offer}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_offer_photo-ad_offer_id', '{{%ad_offer_photo}}');

        $this->renameTable('{{%ad_offer_photo}}', '{{%ad_photo}}');

        $this->addForeignKey(
            'fk-ad_photo_ad_offer_id-ad_offer_id',
            '{{%ad_photo}}',
            'ad_offer_id',
            '{{%ad_offer}}',
            'id'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220929_052007_rename_ad_photo_table cannot be reverted.\n";

        return false;
    }
    */
}
