<b><?= Yii::t('bot', 'Group') ?>: <?= $chatTipQueue->chat->title ?></b><?= $chatTipQueue->chat->username ? ' (@' . $chatTipQueue->chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Confirmation') ?>. <?= Yii::t('bot', 'Financial gifts for this group') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Number of gifts') ?>: <?= $chatTipQueue->userCount; ?><br/>
<br/>
<?= Yii::t('bot', 'Gift amount') ?>: <?= $chatTipQueue->userAmount; ?> <?= $chatTipQueue->currency->code; ?><br/>
