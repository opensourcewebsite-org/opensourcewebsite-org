<?php

use yii\db\Migration;

/**
 * Handles the creation of table `country`.
 */
class m180719_045852_create_country_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('country', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
            'code' => $this->string()->notNull(),
            'slug' => $this->string()->notNull(),
            'wikipedia' => $this->string(),
        ]);

        $this->createIndex('idx-country-name', '{{%country}}', 'name');
        $this->createIndex('idx-country-code', '{{%country}}', 'code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-country-name', '{{%country}}');
        $this->dropIndex('idx-country-code', '{{%country}}');

        $this->dropTable('country');
    }
}
