<?php

use yii\helpers\Html;

?>
<ul class="nav nav-pills ml-auto p-2">
    <li class="nav-item">
        <?php if ($user->getPendingDebts()->count()) : ?>
            <?= Html::a(Yii::t('app', 'Pending'), ['debt/index'], [
                    'class' => 'nav-link show ' . (in_array(Yii::$app->requestedRoute, ['debt/index', 'debt']) ? 'active' : ''),
                ]); ?>
        <?php endif; ?>
    </li>
    <li class="nav-item">
        <?= Html::a(Yii::t('app', 'My deposits'), ['debt/deposit'], [
            'class' => 'nav-link show ' . (in_array(Yii::$app->requestedRoute, ['debt/deposit', 'debt/currency-deposit', 'debt/currency-user-deposit']) ? 'active' : ''),
        ]); ?>
    </li>
    <li class="nav-item">
        <?= Html::a(Yii::t('app', 'My credits'), ['debt/credit'], [
            'class' => 'nav-link show ' . (in_array(Yii::$app->requestedRoute, ['debt/credit', 'debt/currency-credit', 'debt/currency-user-credit']) ? 'active' : ''),
        ]); ?>
    </li>
    <li class="nav-item">
        <?= Html::a(Yii::t('app', 'Archive'), ['debt/archive'], [
            'class' => 'nav-link show ' . (Yii::$app->requestedRoute == 'debt/archive' ? 'active' : ''),
        ]); ?>
    </li>
</ul>
