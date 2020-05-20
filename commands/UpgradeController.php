<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use yii\console\Controller;

class UpgradeController extends Controller
{
    use ControllerLogTrait;

    /**
     * Upgrade DB charset
     */
    public function actionUpgradeDbCharset()
    {
        $connection = \Yii::$app->db;
        preg_match('/' . 'dbname' . '=([^;]*)/', $connection->dsn, $match);
        $dbName = $match[1];
        $command = $connection->createCommand("ALTER DATABASE $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;");
        $command->execute();
        $this->output('DataBase charset is changed!');
    }

    /**
     * Upgrade tables charset
     */
    public function actionUpgradeTablesCharset()
    {
        $this->output('Running tables charset upgrades...');
        $connection = \Yii::$app->db;
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
        $this->output('Tables charset is changed!');
    }
}
