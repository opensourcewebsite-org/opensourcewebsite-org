<?php

use app\models\Rating;

return [
    [
        'id' => 1,
        'user_id' => 1,
        'amount' => 100,
        'type' => Rating::TEAM,
        'created_at' => 1531386000,
    ],
    [
        'id' => 2,
        'user_id' => 1,
        'amount' => 50,
        'type' => Rating::DONATE,
        'created_at' => time(),
    ],
    [
        'id' => 3,
        'user_id' => 5,
        'amount' => 50,
        'type' => Rating::TEAM,
        'created_at' => 1531386000,
    ],
];
