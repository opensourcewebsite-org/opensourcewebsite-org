<?php

use app\models\User;

?>
<b><?= Yii::t('bot', 'Your Rating') ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Rank') ?>:</b> <b><?= $user->getRank() ?></b> <?= Yii::t('bot', 'of') ?> <?= User::getTotalRank(); ?><br/>
<b><?= Yii::t('bot', 'Voting Power') ?>:</b> <b><?= $user->getRatingPercent() ?>%</b> <?= Yii::t('bot', 'of') ?> 100%<br/>
<b><?= Yii::t('bot', 'Rating') ?>:</b> <b><?= $user->getRating() ?></b> <?= Yii::t('bot', 'of') ?> <?= User::getTotalRating(); ?><br/>
<b><?= Yii::t('bot', 'Active Rating') ?>:</b> <b><?= $user->getActiveRating() ?></b> (<?= Yii::t('bot', 'in the last {0,number} days', Yii::$app->settings->days_count_to_calculate_active_rating) ?>)<br/>
<br/>
<?= Yii::t('bot', 'Rating provides') ?>:<br/>
<br/>
  • <?= Yii::t('bot', 'The power of user vote when voting for new features and changing existing features of the Bot and Website') ?>.<br/>
  • <?= Yii::t('bot', 'More possibilities and less limits when using of the Bot and Website') ?>.<br/>
  • <?= Yii::t('bot', 'Passive income, such as dividends from the profits of the Bot and Website, which is distributed proportionally among all users in accordance with the value of their Ratings') ?>.<br/>
<br/>
<b><i><?= Yii::t('bot', 'Your donations and contributions increase your Rating') ?>.</i></b>
