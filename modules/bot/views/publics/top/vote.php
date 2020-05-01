<?= $voter; ?> <?= $userRating ? '(' . $userRating . ') ' : ''; ?><?= Yii::t('bot', 'would like to react'); ?> <?= $candidate; ?> (<?= Yii::t('bot', 'rating'); ?>: <?= $candidateRating; ?>)
