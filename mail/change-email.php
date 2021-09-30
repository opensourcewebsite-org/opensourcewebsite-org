<?php

use yii\helpers\Html;

?>
<h4>Hello<?= !empty($user->name) ? ", {$user->name}!" : '!' ?> Please confirm your email clicking <?= Html::a('here', $link); ?></h4>
<br/><?= $link ?><br/>
Regards,
<p><?= Yii::$app->name; ?></p>
