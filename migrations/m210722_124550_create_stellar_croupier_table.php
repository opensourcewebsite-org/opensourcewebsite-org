<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stellar_croupier}}`.
 */
class m210722_124550_create_stellar_croupier_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stellar_croupier}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull()->unique(),
            'value' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stellar_croupier}}');
    }
}
