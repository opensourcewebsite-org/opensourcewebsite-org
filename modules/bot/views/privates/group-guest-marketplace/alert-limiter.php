<?= Yii::t('bot', 'Your message cannot be sent to the group because') ?>:<br/>
<br/>
<?= Yii::t('bot', 'You are allowed to send messages until {0}', $chatMember->limiter_date) ?>.
