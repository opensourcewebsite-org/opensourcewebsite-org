<b><?= Yii::t('bot', 'Your review') ?></b>.<br/>
<br/>
————<br/>
<br/>
<?php if ($review->text) : ?>
<?= nl2br($review->text) ?><br/>
<?php endif; ?>
<br/>
————<br/>
<?= $review->getStatusLabel() ?> - <?= $review->getStatusInfo() ?>.<br/>
