<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_offer_response}}`.
 */
class m210609_101735_create_ad_offer_response_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_offer_response}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'ad_offer_id' => $this->integer()->unsigned()->notNull(),
            'viewed_at' => $this->integer()->unsigned(),
            'archived_at' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ad_offer_response}}');
    }
}
