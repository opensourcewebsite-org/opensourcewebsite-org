<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stellar_distributor}}`.
 */
class m211103_085718_create_stellar_distributor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stellar_distributor}}', [
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
        $this->dropTable('{{%stellar_distributor}}');
    }
}
