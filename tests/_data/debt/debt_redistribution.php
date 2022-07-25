<?php

use app\models\Contact;
use app\models\DebtRedistribution;
use Helper\debt\redistribution\Common;

$contacts = require 'contact.php';
$result = [];
$model = new DebtRedistribution();

foreach ($contacts as $index => $contactAttributes) {
    $contact = new Contact($contactAttributes);
    $model->setUsers($contact);

    $result[$index] = [
        'user_id' => $model->user_id,
        'link_user_id' => $model->link_user_id,
        'currency_id' => Common::CURRENCY_USD,
        'max_amount' => DebtRedistribution::MAX_AMOUNT_ANY,
    ];
}

return $result;
