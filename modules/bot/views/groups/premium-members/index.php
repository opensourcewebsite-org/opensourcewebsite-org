<?php

use app\components\helpers\Html;
use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Yii::t('bot', 'Group') ?>: <?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?php if ($chat->membership_tag) : ?>
<b>#<?= $chat->membership_tag ?></b><br/>
<?php else : ?>
<b><?= Yii::t('bot', 'Premium members') ?>.</b><br/>
<?php endif; ?>
<br/>
<?php foreach ($members as $member) : ?>
<?= ($count = $member->getPositiveReviewsCount()) ? Html::a(Emoji::LIKE . ' ' . $count, $member->getReviewsLink()) . ' ' : '' ?>
• <?= $member->user->getFullLink(); ?> #ID<?= $member->user->getProviderUserId(); ?><br/>
<?php endforeach; ?>
