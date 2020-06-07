<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestLabel */

$this->title = 'Update Api Test Label: '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Api Test Labels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="api-test-label-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>
</div>
