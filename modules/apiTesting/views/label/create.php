<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestLabel */

$this->title = 'Create Api Test Label';
$this->params['breadcrumbs'][] = ['label' => 'Api Test Labels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-label-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>
</div>
