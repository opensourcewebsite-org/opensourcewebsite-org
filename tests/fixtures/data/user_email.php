<?php

use app\models\User;

return [
    'member' => [
        'id' => 1,
        'user_id' => 1,
        'email' => 'member@localhost.org',
        'confirmed_at' => time(),
    ],
    [
        'id' => 2,
        'user_id' => 2,
        'email' => 'member2@localhost.org',
        'confirmed_at' => null,
    ],
    [
        'id' => 3,
        'user_id' => 3,
        'email' => 'member3@localhost.org',
        'confirmed_at' => time(),
    ],
    [
        'id' => 4,
        'user_id' => 4,
        'email' => 'member4@localhost.org',
        'confirmed_at' => null,
    ],
    [
        'id' => 5,
        'user_id' => 5,
        'email' => 'member5@localhost.org',
        'confirmed_at' => time(),
    ],
];
