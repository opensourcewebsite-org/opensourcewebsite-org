<?php

return [
    [
        'id'           => 1,
        'user_id'      => 100,
        'link_user_id' => 101,
        'name'         => 'Real Contact: 100 => 101',
    ],
    [
        'id'           => 2,
        'user_id'      => 100,
        'link_user_id' => null,
        'name'         => 'Virtual Contact',
    ],
    [
        'id'           => 3,
        'user_id'      => 100,
        'link_user_id' => 999,
        'name'         => 'link_user_id is set, but not real',
    ],
    [
        'id'           => 4,
        'user_id'      => 101,
        'link_user_id' => 100,
        'name'         => 'Real Contact: 101 => 100',
    ],
];
