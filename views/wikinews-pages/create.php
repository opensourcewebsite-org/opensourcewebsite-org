<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\WikinewsPage */

$this->title = 'Create Wikinews Page';
$this->params['breadcrumbs'][] = ['label' => 'Wikinews Pages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wikinews-page-create">

    <?= $this->render('_form', [
        'model' => $model,
        'language_arr' => $language_arr,
    ]) ?>

</div>
