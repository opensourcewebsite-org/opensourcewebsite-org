<?php

use app\models\User;

return [
    'member' => [
        'id' => 1,
        'email' => 'member@localhost.org',
        'auth_key' => 'MMvWBJnS4cnH62hvYJ1pv9bugF7bRPE3',
        'password_hash' => Yii::$app->security->generatePasswordHash('member'),
        'is_email_confirmed' => 1,
        'status' => User::STATUS_ACTIVE,
        'created_at' => 1531386000,
        'updated_at' => 1531386000,
    ],
    [
        'id' => 2,
        'email' => 'member2@localhost.org',
        'auth_key' => 'MMvWBJnS4cnH62hvYJ1pv9bugF7bRPE3',
        'password_hash' => Yii::$app->security->generatePasswordHash('member2'),
        'is_email_confirmed' => 0,
        'status' => User::STATUS_ACTIVE,
        'created_at' => 1531386000,
        'updated_at' => 1531386000,
    ],
    [
        'id' => 3,
        'email' => 'member3@localhost.org',
        'auth_key' => 'MMvWBJnS4cnH62hvYJ1pv9bugF7bRPE3',
        'password_hash' => Yii::$app->security->generatePasswordHash('member3'),
        'is_email_confirmed' => 1,
        'status' => User::STATUS_DELETED,
        'created_at' => 1531386000,
        'updated_at' => 1531386000,
    ],
    [
        'id' => 4,
        'email' => 'member4@localhost.org',
        'auth_key' => 'MMvWBJnS4cnH62hvYJ1pv9bugF7bRPE3',
        'password_hash' => Yii::$app->security->generatePasswordHash('member4'),
        'is_email_confirmed' => 0,
        'status' => User::STATUS_DELETED,
        'created_at' => 1531386000,
        'updated_at' => 1531386000,
    ],
];
