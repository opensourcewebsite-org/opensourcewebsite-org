<?php

use app\models\Contact;
use app\models\DebtRedistributionForm;
use app\widgets\DebtDistributionSettings\DebtRedistributionSettings;

/* @var $this yii\web\View */
/* @var $model   null|DebtRedistributionForm */
/* @var $contact null|Contact */

echo DebtRedistributionSettings::widget([
    'debtRed' => $model,
    'contact' => $contact,
]);
