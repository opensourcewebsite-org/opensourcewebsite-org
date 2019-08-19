<?php

use yii\db\Migration;

/**
 * Handles the creation of table `language`.
 */
class m180719_050741_create_language_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('language', [
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'name_ascii' => $this->string()->notNull(),
            'PRIMARY KEY (code)',
        ]);

        $this->createIndex('idx-language-name', '{{%language}}', 'name');
        $this->createIndex('idx-language-name_ascii', '{{%language}}', 'name_ascii');
        $this->createIndex('idx-language-code', '{{%language}}', 'code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-language-name', '{{%language}}', 'name');
        $this->dropIndex('idx-language-name_ascii', '{{%language}}', 'name_ascii');
        $this->dropIndex('idx-language-code', '{{%language}}', 'code');

        $this->dropTable('language');
    }
}
