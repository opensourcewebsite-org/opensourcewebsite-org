<?php

use app\models\Currency;
use app\models\Resume;
use yii\web\View;

/* @var $this View */
/* @var $model Resume */
/* @var $currencies Currency[] */

$this->title = Yii::t('app', 'Update Resume') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resumes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="resume-update">
    <?= $this->render('_form', [
        'model' => $model,
        'currencies' => $currencies,
    ]); ?>
</div>
