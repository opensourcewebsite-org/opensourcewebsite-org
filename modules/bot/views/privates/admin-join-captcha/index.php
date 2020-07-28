<b><?= $chatTitle ?></b><br/>
<br/>
<?= Yii::t('bot', 'Join Captcha') ?> - <?= Yii::t('bot', 'sends a captcha message to newly joined members') ?>. <?= Yii::t('bot', 'Ignores telegram bots') ?>.<br/>
<br/>
————<br/>
<br/>
<?= $this->render('@bot/views/publics/join-captcha/show-captcha', [
    'user' => $telegramUser,
]); ?>
<br/>
————<br/>
