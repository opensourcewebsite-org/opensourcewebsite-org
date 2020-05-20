<?php

namespace app\commands;

use yii\db\Migration;

class UpgradeController extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        preg_match('/' . 'dbname' . '=([^;]*)/', $this->db->dsn, $match);
        $dbName = $match[1];
        if ($this->execute("ALTER DATABASE $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;")) {
            $tableSchemas = $this->db->schema->getTableSchemas();
            $this->execute("SET foreign_key_checks = 0;");
            foreach ($tableSchemas as $tableSchema) {
                $this->execute("ALTER TABLE $tableSchema->name CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;");
            }
            $this->execute('SET foreign_key_checks = 0;');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "UpgradeController cannot be reverted.\n";

        return false;
    }
}
