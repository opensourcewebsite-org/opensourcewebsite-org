<?php

use app\modules\bot\models\User;
use app\modules\bot\components\helpers\Emoji;

?>
<?= Emoji::KICK_VOTE . ' ' . Yii::t('bot', '{user} has been kicked — the only way to get this user back is for admins to manualy unban in group settings', ['user' => User::getFullLinkByProviderUserId($providerCandidateId)]) ?>.<br/>
<br/>
<?= Yii::t('bot', 'Voters who chose to kick') ?>:<br/>
<?php foreach ($voterIds as $providerVoterId) : ?>
  <?= User::getFullLinkByProviderUserId($providerVoterId); ?><br/>
<?php endforeach; ?>
