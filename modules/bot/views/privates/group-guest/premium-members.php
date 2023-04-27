<b><?= Yii::t('bot', 'Group') ?>: <?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<b><?= Yii::t('bot', 'Premium members') ?>.</b><br/>
<?php if ($chat->membership_tag) : ?>
<br/>
<b><?= Yii::t('bot', 'Members status') ?>:</b> <?= $chat->membership_tag ?><br/>
<?php endif; ?>
————<br/>
<i><?= Yii::t('bot', 'Sorted by user rank') ?>.</i>
