<b><?= $chatTitle ?></b><br/>
<br/>
<?= Yii::t('bot', 'Join Captcha') ?> - <?= Yii::t('bot', 'sends a captcha message to newly joined members, which waits for the member action for {0,number} mins and after this period expires, excludes the member from the group', 5) ?>. <?= Yii::t('bot', 'Ignores telegram bots') ?>.<br/>
<br/>
————<br/>
<br/>
<?= $this->render('@bot/views/groups/join-captcha/show-captcha', [
    'user' => $telegramUser,
]); ?>
<br/>
————<br/>
