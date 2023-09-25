<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<?php if ($chat->description) : ?>
<br/>
<?= nl2br($chat->description); ?><br/>
<?php endif; ?>
<?php if ($chat->isInviterOn()) : ?>
————<br/>
<?= Yii::t('bot', 'Reward amount for adding a new member to the group') ?>: <?= $chat->getDisplayRewardAmount() ?><br/>
<?php endif; ?>
