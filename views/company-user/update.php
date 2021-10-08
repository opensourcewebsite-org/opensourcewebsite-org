<?php

use app\models\Company;
use yii\web\View;

/**
 * @var View $this
 * @var Company $model
 * @var array $_params_
 */

$this->title = Yii::t('app', 'Update Company') . ' #' . $model->id;;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="company-update">
    <?= $this->render('_form', $_params_); ?>
</div>
