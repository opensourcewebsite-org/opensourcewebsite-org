<?php

use app\models\User;

?>
<b>OSW <?= Yii::t('bot', 'Rank') ?>:</b> <b><?= $user->getRank() ?></b> <?= Yii::t('bot', 'of') ?> <?= User::getTotalRank(); ?><br/>
