<?php

use app\models\Contact;

$priorityList = [
    1 => 1,
    2 => 2,
    255 => 255,
    0 => Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY,
];

return [
    //Redistribution chain with priority #1
    'Chain Priority #1. Member: 1st' => [
        'user_id' => 202,
        'link_user_id' => 203,
        'debt_redistribution_priority' => $priorityList[1],
        'name' => "Contact for debt Redistribution chain. Priority: #{$priorityList[1]}. Member: 1st",
    ],
    'Chain Priority #1. Member: LAST' => [
        'user_id' => 203,
        'link_user_id' => 201,
        'debt_redistribution_priority' => $priorityList[1],
        'name' => "Contact for debt Redistribution chain. Priority: #{$priorityList[1]}. Member: LAST",
    ],

    //Redistribution chain with priority #2
    'Chain Priority #2. Member: 1st' => [
        'user_id' => 202,
        'link_user_id' => 204,
        'debt_redistribution_priority' => $priorityList[2],
        'name' => "Contact for debt Redistribution chain. Priority: #{$priorityList[2]}. Member: 1st",
    ],
    'Chain Priority #2. Member: LAST' => [
        'user_id' => 204,
        'link_user_id' => 201,
        'debt_redistribution_priority' => $priorityList[2],
        'name' => "Contact for debt Redistribution chain. Priority: #{$priorityList[2]}. Member: LAST",
    ],

    //Redistribution chain with priority #255
    'Chain Priority #255. Member: 1st' => [
        'user_id' => 202,
        'link_user_id' => 205,
        'debt_redistribution_priority' => $priorityList[255],
        'name' => "Contact for debt Redistribution chain. Priority: #{$priorityList[255]}. Member: 1st",
    ],
    'Chain Priority #255. Member: LAST' => [
        'user_id' => 205,
        'link_user_id' => 201,
        'debt_redistribution_priority' => $priorityList[255],
        'name' => "Contact for debt Redistribution chain. Priority: #{$priorityList[255]}. Member: LAST",
    ],

    //Redistribution chain with priority #0 (Deny)
    'Chain Priority #0 (Deny). Member: 1st' => [
        'user_id' => 202,
        'link_user_id' => 206,
        'debt_redistribution_priority' => $priorityList[0],
        'name' => "Contact for debt Redistribution chain. Priority: #{$priorityList[0]}. Member: 1st",
    ],
    'Chain Priority #0 (Deny). Member: LAST' => [
        'user_id' => 206,
        'link_user_id' => 201,
        'debt_redistribution_priority' => $priorityList[0],
        'name' => "Contact for debt Redistribution chain. Priority: #{$priorityList[0]}. Member: LAST",
    ],
];
