<?php

use app\modules\bot\models\User;

?>
<?= User::getFullLinkByProviderUserId($providerVoterId); ?> <?= $userRating ? '(' . $userRating . ') ' : ''; ?><?= Yii::t('bot', 'wrote <code>{0}</code> and reacted to a message from', $command); ?> <?= User::getFullLinkByProviderUserId($providerCandidateId); ?><?= $candidateRating ? ' (' . $candidateRating . ') ' : ''; ?><br/>
