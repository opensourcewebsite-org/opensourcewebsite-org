<?php

use yii\helpers\Html;

?>
<h4>Hello<?= !empty($user->name) ? ', ' . $user->name : '' ?>!</h4><br/>
<br/>
Please confirm your email clicking <?= Html::a('here', $link); ?>.<br/>
<br/>
Thanks,
<p><?= Yii::$app->name ?></p>
