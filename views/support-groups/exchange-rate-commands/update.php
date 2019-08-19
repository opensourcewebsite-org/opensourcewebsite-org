<?php

use yii\helpers\Html;
use app\components\helpers\SupportGroupHelper;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupExchangeRate */

$this->title = Yii::t('app', 'Update ' . SupportGroupHelper::getExchangeRateCommandType($type));

?>
<div class="support-group-exchange-rate-command-update">

    <?= $this->render('_form', [
        'type' => $type,
        'model' => $model,
        'supportGroupExchangeRateId' => $supportGroupExchangeRateId,
    ]); ?>

</div>
