<?php

use app\models\User;

$users = [];
for ($i = 1; $i <= 6; ++$i) {
    $id = 200 + $i;

    $users[] = [
        'id' => $id,
        'username' => "debtRedistribution_$id",
        'auth_key' => "test{$id}key",
        'password_hash' => Yii::$app->security->generatePasswordHash("debtRedistribution_$id"),
        'email' => "debtRedistribution_$id@example.com",
        'is_authenticated' => 1,
        'status' => User::STATUS_ACTIVE,
        'created_at' => time(),
        'updated_at' => time(),
        'last_activity_at' => time(),
    ];
}

return $users;
