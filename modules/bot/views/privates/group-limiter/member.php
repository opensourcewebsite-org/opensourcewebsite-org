<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->user->getFullLink() ?><br/>
<br/>
<?= Yii::t('bot', 'Verification is valid until') ?>: <?= $chatMember->limiter_date ?><br/>
————<br/>
<?= Yii::t('bot', 'Send any date in format «YYYY-MM-DD» to change the date') ?>.<br/>
