<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Debt */

$this->title = Yii::t('app', 'Create Debt');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Debts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="debt-create">

    <?= $this->render('_form', [
        'model' => $model,
        'user' => $user,
        'currency' => $currency,
    ]); ?>

</div>
