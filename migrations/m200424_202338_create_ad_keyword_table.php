<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ad_keyword}}`.
 */
class m200424_202338_create_ad_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ad_keyword}}', [
            'id' => $this->primaryKey()->unsigned(),
            'keyword' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ad_keyword}}');
    }
}
