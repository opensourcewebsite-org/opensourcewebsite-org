<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->membership_date . ' - ' . $chatMember->user->getFullLink(); ?><br/>
————<br/>
<?= Yii::t('bot', 'Send any date in format «YYYY-MM-DD» to change the date') ?>.<br/>
