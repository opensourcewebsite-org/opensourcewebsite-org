<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Captcha') ?> - <?= Yii::t('bot', 'sends a private message with a captcha to new members who have applied to join the group') ?>. <?= Yii::t('bot', 'Works only when the «Approve New Members» mode is active') ?>. <?= Yii::t('bot', 'Ignores bots') ?>.<br/>
————<br/>
<?= $this->render('@bot/views/groups/join-captcha/show-captcha', [
    'user' => $user,
]); ?>
————<br/>
