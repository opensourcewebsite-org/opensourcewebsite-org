<?= Yii::t('bot', 'Your last message in the group «{0}» was deleted due because the admins not allowing you to send messages since {1}', [$chat->title, $chatMember->limiter_date]) ?>.<br/>
<br/>
<?= Yii::t('bot', 'Contact the group admins for details') ?>.
