<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_search_response}}`.
 */
class m210609_101743_create_ad_search_response_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_search_response}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'ad_search_id' => $this->integer()->unsigned()->notNull(),
            'viewed_at' => $this->integer()->unsigned(),
            'archived_at' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ad_search_response}}');
    }
}
