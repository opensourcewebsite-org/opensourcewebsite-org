<?php

use yii\db\Migration;

/**
 * Class m190627_143347_update_table_charset
 */
class m190627_143347_update_table_charset extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchemas = $this->db->schema->getTableSchemas();
        $this->execute("SET foreign_key_checks = 0;");
        foreach ($tableSchemas as $tableSchema) {
            $this->execute("ALTER TABLE $tableSchema->name CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        }
        $this->execute("SET foreign_key_checks = 0;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190627_143347_update_table_charset cannot be reverted.\n";

        return false;
    }
}
