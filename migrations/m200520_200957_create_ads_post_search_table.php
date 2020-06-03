<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ads_post_search}}`.
 */
class m200520_200957_create_ads_post_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ads_post_search}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'radius' => $this->integer()->unsigned(),
            'location_latitude' => $this->string(),
            'location_longitude' => $this->string(),
        ]);

        $this->addForeignKey(
            'fk-ads_post_search_user_id-bot_user_id',
            '{{%ads_post_search}}',
            'user_id',
            '{{%bot_user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ads_post_search_user_id-bot_user_id');
        
        $this->dropTable('{{%ads_post_search}}');
    }
}
