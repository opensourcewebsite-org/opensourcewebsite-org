<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Captcha') ?> - <?= Yii::t('bot', 'sends a private message with a captcha to new members who have applied to join the group') ?>. <?= Yii::t('bot', 'Works only when the «Approve New Members» mode is active') ?>. <?= Yii::t('bot', 'Ignores bots') ?>.<br/>
<?php if ($chat->join_captcha_link_to_rules) : ?>
<br/>
<?= Yii::t('bot', 'Link to Rules') ?>: <?= $chat->join_captcha_link_to_rules ?><br/>
<?php endif; ?>
————<br/>
<?= $this->render('show-captcha', [
    'chat' => $chat,
    'message' => $chat->join_captcha_message,
]); ?>
————<br/>
