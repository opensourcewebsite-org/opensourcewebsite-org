<?php if ($changeRequest) : ?>
    <?= \Yii::t('bot', 'You email is almost set. Please, check your email for confirmation letter.') ?>
<?php elseif ($mergeRequest) : ?>
    <?= \Yii::t('bot', 'We found a user with the same email as you entered. Do you want to merge your accounts?') ?>
<?php else : ?>
    <?= $error ?>
<?php endif; ?>
