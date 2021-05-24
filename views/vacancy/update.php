<?php

use app\models\Currency;
use app\models\Vacancy;
use yii\web\View;

/* @var $this View */
/* @var $model Vacancy */
/* @var $currencies Currency[] */

$this->title = Yii::t('app', 'Update Vacancy') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vacancies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="vacancy-update">
    <?= $this->render('_form', [
        'model' => $model,
        'currencies' => $currencies,
    ]); ?>
</div>
