<?php

use app\models\Debt;

$contacts = require 'contact.php';

return [
    "It's balance should be redistributed" => [
        'currency_id' => 108, //USD
        'from_user_id' => 201,
        'to_user_id' => 202,
        'amount' => 5000.55,
        'status' => Debt::STATUS_CONFIRM,
    ],
    //This balance should NOT be redistributed.
    //It will INCREASE after redistribution, if CHAIN #1 was used.
    //And nevertheless it belongs to Chain #1 - chain should be still capable to receive Redistribution
    "It's balance belongs to: Chain Priority #1. Member: 1st" => [
        'currency_id' => 108, //USD
        'from_user_id' => $contacts['Chain Priority #1. Member: 1st']['link_user_id'],
        'to_user_id' => $contacts['Chain Priority #1. Member: 1st']['user_id'],
        'amount' => 11111.11,
        'status' => Debt::STATUS_CONFIRM,
    ],
    //This balance should NOT be redistributed.
    //It will DECREASE after redistribution, if CHAIN #2 was used.
    "It's balance belongs to: Chain Priority #2. Member: LAST" => [
        'currency_id' => 108,   //USD
        'from_user_id' => $contacts['Chain Priority #2. Member: LAST']['user_id'],
        'to_user_id' => $contacts['Chain Priority #2. Member: LAST']['link_user_id'],
        'amount' => 22222.22,      //tests expect it is greater than amount of "It's balance should be redistributed"
        'status' => Debt::STATUS_CONFIRM,
    ],
];
