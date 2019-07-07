<?php

/* @var $this yii\web\View */
/* @var $model app\models\Debt */

$this->title = Yii::t('app', $model->currency->code);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Debts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
<div class="debt-view">
    <ul id="debt-tabs" class="nav nav-pills p-2">
        <li class="nav-item">
            <a class="nav-link active" href="#deposit-tab" data-toggle="tab">Deposits</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#credit-tab" data-toggle="tab">Credits</a>
        </li>
    </ul>
    <div class="tab-content p-0">
        <div id="deposit-tab" class="tab-pane active">
            <?= $this->render('deposit', [
                'depositDataProvider' => $depositDataProvider,
            ]); ?>
        </div>
        <div id="credit-tab" class="tab-pane">
            <?= $this->render('credit', [
                'creditDataProvider' => $creditDataProvider,
            ]); ?>
        </div>
    </div>
</div>
