<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . getenv('DB_HOST', 'localhost') . ';port=' . getenv('DB_PORT', '3306') . ';dbname=' . getenv('DB_NAME', 'opensourcewebsite'),
    'username' => getenv('DB_USERNAME', 'root'),
    'password' => getenv('DB_PASSWORD', ''),
    'charset' => getenv('DB_CHARSET', 'utf8mb4'),
    'attributes' => [
        PDO::ATTR_CASE => PDO::CASE_LOWER,
    ],
    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
