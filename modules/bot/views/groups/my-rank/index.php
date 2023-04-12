<?php

use app\models\User;

?>
<?php if ($user) : ?>
<b>OSW <?= Yii::t('bot', 'Rank') ?></b>: <b><?= $user->getRank() ?></b> <?= Yii::t('bot', 'of') ?> <?= User::getTotalRank(); ?><br/>
<?php endif; ?>
