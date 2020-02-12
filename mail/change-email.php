<?php

use yii\helpers\Html;

?>

<h4>Hello<?= !empty($user->name) ? ", {$user->name}!" : '!' ?> Please confirm changing your email clicking <?php echo Html::a('here', $link); ?></h4>

Regards,
<p><?= Yii::$app->name; ?></p>