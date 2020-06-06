<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_order_keyword}}`.
 */
class m200424_202421_create_ad_order_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_order_keyword}}', [
            'ad_order_id' => $this->integer()->unsigned()->notNull(),
            'ad_keyword_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ad_order_keyword_ad_order_id-ad_order_id',
            '{{%ad_order_keyword}}',
            'ad_order_id',
            '{{%ad_order}}',
            'id'
        );

        $this->addForeignKey(
            'fk-ad_order_keyword_ad_keyword_id-ad_keyword_id',
            '{{%ad_order_keyword}}',
            'ad_keyword_id',
            '{{%ad_keyword}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ad_order_keyword_ad_order_id-ad_order_id');

        $this->dropForeignKey('fk-ad_order_keyword_ad_keyword_id-ad_keyword_id');

        $this->dropTable('{{%ad_order_keyword}}');
    }
}
