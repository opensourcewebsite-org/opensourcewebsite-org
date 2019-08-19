<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupExchangeRate */

$this->title = Yii::t('app', 'Update Exchange Rate');

?>
<div class="support-group-exchange-rate-update">

    <?= $this->render('_form', [
        'model' => $model,
        'supportGroupId' => $supportGroupId,
    ]); ?>

</div>
