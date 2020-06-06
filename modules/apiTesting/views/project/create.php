<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestProject */

$this->title = 'Create Api Test Project';
$this->params['breadcrumbs'][] = ['label' => 'Api Test Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-project-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>
</div>
