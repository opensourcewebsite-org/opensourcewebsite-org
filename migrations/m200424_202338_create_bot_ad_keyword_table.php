<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_ad_keyword}}`.
 */
class m200424_202338_create_bot_ad_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_ad_keyword}}', [
            'id' => $this->primaryKey()->unsigned(),
            'word' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bot_ad_keyword}}');
    }
}
