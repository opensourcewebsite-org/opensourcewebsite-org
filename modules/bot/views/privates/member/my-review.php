<b><?= Yii::t('bot', 'Your public review') ?>.</b><br/>
<?php if ($review->text) : ?>
————<br/>
<?= nl2br($review->text) ?><br/>
<?php endif; ?>
————<br/>
<?= $review->getStatusLabel() ?> - <?= $review->getStatusInfo() ?>.<br/>
