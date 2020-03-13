<?php if ($changeRequest) : ?>
<?= Yii::t('bot', 'You email is almost set. Please, check your email for confirmation letter.') ?>
<?php elseif ($mergeRequest) : ?>
<?= Yii::t('bot', 'This email is already taken') ?>. <?= Yii::t('bot', 'Do you want to merge your accounts?') ?>
<?php else : ?>
<?= $error ?>
<?php endif; ?>
