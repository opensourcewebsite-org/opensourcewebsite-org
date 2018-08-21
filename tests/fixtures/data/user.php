<?php

return [
    [
        'id' => 100,
        'username' => 'admin',
        'auth_key' => 'test100key',
        'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
        'email' => 'admin@example.com',
        'created_at' => time(),
        'updated_at' => time(),
    ],
    [
        'id' => 101,
        'username' => 'webmaster',
        'auth_key' => 'test101key',
        'password_hash' => Yii::$app->security->generatePasswordHash('webmaster'),
        'email' => 'demo@example.com',
        'created_at' => time(),
        'updated_at' => time(),
    ],
];