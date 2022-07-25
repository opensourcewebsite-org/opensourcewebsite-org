<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Captcha') ?> - <?= Yii::t('bot', 'sends a captcha message in response to the first message of the joined members, which waits for the member action for {0,number} mins and after this period expires, excludes the member from the group', 5) ?>. <?= Yii::t('bot', 'Ignores bots') ?>.<br/>
<br/>
————<br/>
<br/>
<?= $this->render('@bot/views/groups/join-captcha/show-captcha', [
    'user' => $telegramUser,
]); ?>
<br/>
————<br/>
