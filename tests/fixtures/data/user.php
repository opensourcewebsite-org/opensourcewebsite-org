<?php

use app\models\User;

return [
    'member' => [
        'id' => 1,
        'auth_key' => 'MMvWBJnS4cnH62hvYJ1pv9bugF7bRPE3',
        'password_hash' => Yii::$app->security->generatePasswordHash('member'),
        'status' => User::STATUS_ACTIVE,
        'rating' => 150,
        'created_at' => 1531386000,
        'updated_at' => 1531386000,
        'last_activity_at' => 1531386000,
    ],
    [
        'id' => 2,
        'auth_key' => 'MMvWBJnS4cnH62hvYJ1pv9bugF7bRPE3',
        'password_hash' => Yii::$app->security->generatePasswordHash('member2'),
        'status' => User::STATUS_ACTIVE,
        'created_at' => 1531386001,
        'updated_at' => 1531386001,
        'last_activity_at' => 1531386001,
    ],
    [
        'id' => 3,
        'auth_key' => 'MMvWBJnS4cnH62hvYJ1pv9bugF7bRPE3',
        'password_hash' => Yii::$app->security->generatePasswordHash('member3'),
        'status' => User::STATUS_DELETED,
        'created_at' => 1531386002,
        'updated_at' => 1531386002,
        'last_activity_at' => 1531386002,
    ],
    [
        'id' => 4,
        'auth_key' => 'MMvWBJnS4cnH62hvYJ1pv9bugF7bRPE3',
        'password_hash' => Yii::$app->security->generatePasswordHash('member4'),
        'status' => User::STATUS_DELETED,
        'created_at' => 1531386003,
        'updated_at' => 1531386003,
        'last_activity_at' => 1531386003,
    ],
    [
        'id' => 5,
        'auth_key' => 'MMvWBJnS4cnH62hvYJ1pv9bugF7bRPE3',
        'password_hash' => Yii::$app->security->generatePasswordHash('member5'),
        'status' => User::STATUS_ACTIVE,
        'rating' => 50,
        'created_at' => 1531386005,
        'updated_at' => 1531386005,
        'last_activity_at' => 1531386005,
    ],
];
