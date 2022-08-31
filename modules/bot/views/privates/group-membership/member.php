<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->membership_date . ' - ' . $chatMember->user->getFullLink(); ?><br/>
<?php if ($chatMember->membership_note) : ?>
<br/>
<?= Yii::t('bot', 'Note') ?>: <?= $chatMember->membership_note ?><br/>
<?php endif; ?>
————<br/>
<?= Yii::t('bot', 'Send any date in format «YYYY-MM-DD» to change the date') ?>.<br/>
