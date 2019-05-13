<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wikinews_language}}`.
 */
class m190311_150519_create_wikinews_language_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wikinews_language}}', [
            'id' => $this->primaryKey()->unsigned(),
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wikinews_language}}');
    }
}
