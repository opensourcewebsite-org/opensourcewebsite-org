<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Greeting') ?> - <?= Yii::t('bot', 'sends a welcome message to newly joined members') ?>. <?= Yii::t('bot', 'Ignores bots') ?>.<br/>
<br/>
————<br/>
<br/>
<?= $this->render('@bot/views/groups/greeting/show-greeting', [
    'user' => $telegramUser,
    'message' => $chat->greeting_message,
]); ?>
<br/>
————<br/>
