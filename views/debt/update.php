<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Debt */

$this->title = Yii::t('app', 'Update Debt: ' . $model->id, [
        'nameAttribute' => '' . $model->id,
    ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Debts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="debt-update">

    <?= $this->render('_form', [
        'model' => $model,
        'user' => $user,
        'currency' => $currency,
    ]); ?>

</div>
