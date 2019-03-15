<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\WikinewsPage */

$this->title = 'Update Wikinews Page: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Wikinews Pages', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="wikinews-page-update">


    <?= $this->render('_form', [
        'model' => $model,
        'language_arr' => $language_arr,
    ]) ?>

</div>
