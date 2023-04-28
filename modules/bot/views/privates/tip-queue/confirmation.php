<b><?= Yii::t('bot', 'Multitip confirmation') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Group') ?>: <b><?= $chatTipQueue->chat->title ?></b> <?= $chatTipQueue->chat->username ? ' (@' . $chatTipQueue->chat->username . ')' : '' ?>
<br/>
<?= Yii::t('bot', 'User amount') ?>: <?= $chatTipQueue->userAmount; ?> <?= $chatTipQueue->currency->code; ?>
<br/>
<?= Yii::t('bot', 'User count') ?>: <?= $chatTipQueue->userCount; ?>
<br/>
