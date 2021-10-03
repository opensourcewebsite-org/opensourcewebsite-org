<?php

use yii\helpers\Html;
use app\models\Contact;

?>
<ul class="nav nav-pills ml-auto p-2">
    <li class="nav-item">
        <?= Html::a(Yii::t('app', 'Users'), ['contact/index'], [
            'class' => 'nav-link show ' . (Yii::$app->requestedRoute == 'contact/index' ? 'active' : ''),
        ]); ?>
    </li>
    <li class="nav-item">
        <?= Html::a(Yii::t('app', 'Non-Users'), ['contact/non-users'], [
            'class' => 'nav-link show ' . (Yii::$app->requestedRoute == 'contact/non-users' ? 'active' : '')
        ]); ?>
    </li>
    <li class="nav-item">
        <?= Html::a(Yii::t('app', 'Groups'), ['contact/group'], [
            'class' => 'nav-link show ' . (Yii::$app->requestedRoute == 'contact/group' ? 'active' : ''),
        ]); ?>
    </li>
</ul>
