<?php

use yii\helpers\Html;

?>
Hello<?= !empty($user->name) ? ', ' . $user->name : '' ?>!

Please confirm your email clicking <?= $link ?>.

Thanks,
<?= Yii::$app->name ?>
