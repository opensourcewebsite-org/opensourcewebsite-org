<b><?= $chatTitle ?></b><br/>
<br/>
<?= Yii::t('bot', 'Greeting') ?> - <?= Yii::t('bot', 'sends a welcome message to newly joined members, which is automatically deleted after {0,number} mins', 30) ?>. <?= Yii::t('bot', 'Ignores bots') ?>.<br/>
<br/>
————<br/>
<br/>
<?= $this->render('@bot/views/groups/greeting/show-greeting', [
    'user' => $telegramUser,
    'message' => $messageSetting->value,
]); ?>
<br/>
————<br/>
