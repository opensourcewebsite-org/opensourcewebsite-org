<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_stellar}}`.
 */
class m210619_135841_create_user_stellar_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_stellar}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'public_key' =>$this->string()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'confirmed_at' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_stellar}}');
    }
}
