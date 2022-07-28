<b><?= Yii::t('bot', 'Your public review') ?></b>.<br/>
————<br/>
<br/>
<?php if ($review->text) : ?>
<?= nl2br($review->text) ?><br/>
<?php endif; ?>
<br/>
————<br/>
<?= $review->getStatusLabel() ?> - <?= $review->getStatusInfo() ?>.<br/>
