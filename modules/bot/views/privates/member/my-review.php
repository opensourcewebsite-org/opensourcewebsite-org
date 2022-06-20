<b><?= Yii::t('bot', 'Review') ?></b>.<br/>
<br/>
————<br/>
<br/>
<?php if ($review->text) : ?>
<?= nl2br($review->text) ?><br/>
<?php endif; ?>
<br/>
————<br/>
<?= $review->getStatusLabel() ?> - <?= $review->getStatusInfo() ?>.<br/>
