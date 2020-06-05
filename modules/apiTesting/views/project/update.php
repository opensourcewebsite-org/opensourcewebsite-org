<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestProject */

$this->title = 'Update Api Test Project: '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="api-test-project-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
