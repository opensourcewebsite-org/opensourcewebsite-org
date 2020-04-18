<?php

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroup */

$this->title = 'Create Support Group';
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="support-group-create">

    <?= $this->render('_form', [
        'model' => $model,
        'langs' => $langs,
        'languages' => $languages,
    ]) ?>

</div>
