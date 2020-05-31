<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_ad_category}}`.
 */
class m200424_202124_create_bot_ad_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_ad_category}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bot_ad_category}}');
    }
}
