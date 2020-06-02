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
            'ads_post_id' => $this->integer()->unsigned()->notNull(),
            'file_id' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ad_photo}}');
    }
}
