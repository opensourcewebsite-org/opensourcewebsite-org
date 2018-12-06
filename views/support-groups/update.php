<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroup */

$this->title = 'Update Support Group: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="support-group-update">

    <?= $this->render('_form', [
        'model' => $model,
        'langs' => $langs,
    ]) ?>

</div>
