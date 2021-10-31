<?php

use yii\helpers\Html;

?>
<ul class="nav nav-pills ml-auto p-2">
    <li class="nav-item">
        <?= Html::a(
    Yii::t('app', 'Info'),
    ['/stellar-basic-income'],
    [
                'class' => 'nav-link show ' . (in_array(Yii::$app->requestedRoute, ['stellar-basic-income/index', 'stellar-basic-income']) ? 'active' : ''),
            ]
);
        ?>
    </li>
    <li class="nav-item">
        <?= Html::a(
            Yii::t('app', 'Participants'),
            ['/stellar-basic-income/participant'],
            [
                'class' => 'nav-link show ' . (Yii::$app->requestedRoute == 'stellar-basic-income/participant' ? 'active' : '')
            ]
        );
        ?>
    </li>
    <li class="nav-item">
        <?= Html::a(
            Yii::t('app', 'Candidates'),
            ['/stellar-basic-income/candidate'],
            [
                'class' => 'nav-link show ' . (Yii::$app->requestedRoute == 'stellar-basic-income/candidate' ? 'active' : '')
            ]
        );
        ?>
    </li>
</ul>
