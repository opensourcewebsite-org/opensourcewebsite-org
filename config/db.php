<?php

$name = getenv('DB_NAME') ?: 'opensourcewebsite';
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

return [
    'class' => 'yii\db\Connection',
    'dsn' => "mysql:host=$host;port=$port;dbname=$name",
    'username' => $username,
    'password' => $password,
    'charset' => $charset,
    'attributes' => [PDO::ATTR_CASE => PDO::CASE_LOWER],

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
