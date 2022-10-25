<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Premium membership') ?> - <?= Yii::t('bot', 'gives selected members additional privileges in the group') ?>. <?= Yii::t('bot', 'Ignores bots') ?>.<br/>
<?php if ($chat->membership_tag) : ?>
<br/>
<?= Yii::t('bot', 'Tag for members') ?>: <?= $chat->membership_tag ?><br/>
<?php endif; ?>
