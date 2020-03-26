<?php

use app\models\PaymentMethod;

return [
    [
        'id' => 1,
        'name' => 'E-Money 1',
        'type' => PaymentMethod::TYPE_EMONEY,
    ],
    [
        'id' => 2,
        'name' => 'E-Money 2',
        'type' => PaymentMethod::TYPE_EMONEY,
    ],
    [
        'id' => 3,
        'name' => 'Bank 1',
        'type' => PaymentMethod::TYPE_BANK,
    ],
    [
        'id' => 4,
        'name' => 'Bank 2',
        'type' => PaymentMethod::TYPE_BANK,
    ],
];
