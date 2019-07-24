<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupExchangeRate */

$this->title = Yii::t('app', 'Update Exchange Rate: ' . $model->id, [
        'nameAttribute' => '' . $model->id,
    ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Exchange Rates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="support-group-exchange-rate-update">

    <?= $this->render('_form', [
        'model' => $model,
        'supportGroupId' => $supportGroupId,
    ]); ?>

</div>
