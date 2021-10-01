<?php

use yii\helpers\Html;

?>
<h4>Hello<?= !empty($user->name) ? ', ' . $user->name : '' ?>!</h4><br/>
<br/>
Someone has requested a link to reset your password. To reset your password please visit the following link <?= Html::a('Reset password', $link); ?>.<br/>
<br/>
If you ignore this message, your password will not be changed.<br/>
<br/>
Thanks,
<p><?= Yii::$app->name ?></p>
