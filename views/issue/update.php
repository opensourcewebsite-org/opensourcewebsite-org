<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $issue app\issues\Issue */

$this->title = Yii::t('app', 'Update Issue: ' . $issue->title, [
    'nameAttribute' => '' . $issue->title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Issues'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $issue->title, 'url' => ['view', 'id' => $issue->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="issue-update">

    <?= $this->render('_form', [
        'issue' => $issue,
    ]) ?>

</div>
