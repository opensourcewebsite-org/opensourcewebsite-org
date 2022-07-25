<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stellar_giver}}`.
 */
class m211103_085727_create_stellar_giver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stellar_giver}}', [
            'id' => $this->primaryKey()->unsigned(),
            'key' => $this->string()->notNull()->unique(),
            'value' => $this->string(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stellar_giver}}');
    }
}
