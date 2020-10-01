<?php

use app\modules\bot\models\User;

?>
<?= User::getFullLinkByProviderUserId($providerVoterId); ?> <?= $userRating ? '(' . $userRating . ') ' : ''; ?><?= Yii::t('bot', 'wrote <code>{0}</code> and would like to kick', $command); ?> <?= User::getFullLinkByProviderUserId($providerCandidateId); ?><?= $candidateRating ? ' (' . $candidateRating . ')' : ''; ?>
