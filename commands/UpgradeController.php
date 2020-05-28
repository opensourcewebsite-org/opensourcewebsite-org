<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class UpgradeController extends Controller
{
    /**
     * Upgrade database charset to utf8mb4 COLLATE utf8mb4_0900_ai_ci
     */
    public function actionDatabaseCharset()
    {
        echo "Running database charset upgrades.\n";

        $connection = Yii::$app->db;
        preg_match('/' . 'dbname' . '=([^;]*)/', $connection->dsn, $match);
        $dbName = $match[1];
        $command = $connection->createCommand("ALTER DATABASE $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;");
        $command->execute();

        echo "Database charset is changed.\n";
    }

    /**
     * Upgrade tables charset to utf8mb4 COLLATE utf8mb4_0900_ai_ci
     */
    public function actionTablesCharset()
    {
        echo "Running tables charset upgrades.\n";

        $connection = Yii::$app->db;
        $tableSchemas = $connection->schema->getTableSchemas();
        $sqlForeignKeyChecks = 'SET foreign_key_checks = 0;';
        $command = $connection->createCommand($sqlForeignKeyChecks);
        $command->execute();
        foreach ($tableSchemas as $tableSchema) {
            $sqlTablesSchemas = "ALTER TABLE $tableSchema->name CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;";
            $command = $connection->createCommand($sqlTablesSchemas);
            $command->execute();
        }
        $command = $connection->createCommand($sqlForeignKeyChecks);
        $command->execute();

        echo "Tables charset is changed.\n";
    }
}
