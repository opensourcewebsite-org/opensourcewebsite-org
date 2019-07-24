<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupExchangeRate */

$this->title = Yii::t('app', 'Create Exchange Rate');

?>
<div class="support-group-exchange-rate-create">

    <?= $this->render('_form', [
        'model' => $model,
        'supportGroupId' => $supportGroupId,
    ]); ?>

</div>
