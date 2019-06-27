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
        $table_schemas = $this->db->schema->getTableSchemas();
        foreach ($table_schemas as $table_schema) {
            $this->execute("ALTER TABLE $table_schema->name CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        }
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
