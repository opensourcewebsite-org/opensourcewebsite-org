<?php

use app\models\DebtRedistribution;

$contacts = require 'contact.php';
$result = [];

foreach ($contacts as $index => $contact) {
    $result[$index] = [
        'user_id' => $contact['link_user_id'],
        'link_user_id' => $contact['user_id'],
        'currency_id' => 108, //USD
        'max_amount' => DebtRedistribution::MAX_AMOUNT_ANY,
    ];
}

return $result;
