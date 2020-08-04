<?php

use app\modules\bot\models\User;
use app\modules\bot\components\helpers\Emoji;

?>
<?= Emoji::SAVE_VOTE . ' ' . Yii::t('bot', '{user} has been saved — no kick for you this time', ['user' => User::getFullLinkByProviderUserId($providerCandidateId)]) ?>.<br/>
<br/>
<?= Yii::t('bot', 'Voters who chose to save') ?>:<br/>
<?php foreach ($voterIds as $providerVoterId) : ?>
  <?= User::getFullLinkByProviderUserId($providerVoterId); ?><br/>
<?php endforeach; ?>
