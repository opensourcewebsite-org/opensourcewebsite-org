<?= $voter; ?> <?= $userRating ? '(' . $userRating . ') ' : ''; ?><?= Yii::t('bot', 'reacted to a message from'); ?> <?= $candidate; ?><?= $candidateRating ? ' (' . $candidateRating . ') ' : ''; ?>
