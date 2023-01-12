<?php

use app\modules\bot\components\helpers\Emoji;

/**
 * @var $changedAttributes mixed
 * @var $user \app\modules\bot\models\User
 */
?>
<b><?= Yii::t('bot', 'User changed account information'); ?></b>.<br/>
<br/>
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $user->getIdFullLink() ?><br/>
<b><?= Yii::t('bot', 'Username') ?></b>: <?= isset($changedAttributes->provider_user_name) ? $changedAttributes->provider_user_name . ' ' . Emoji::RIGHT . ' ' : ''; ?><?= $user->provider_user_name ?><br/>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= isset($changedAttributes->provider_user_first_name) ? $changedAttributes->provider_user_first_name . ' ' . Emoji::RIGHT . ' ' : ''; ?><?= $user->provider_user_first_name ?><br/>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= isset($changedAttributes->provider_user_last_name) ? $changedAttributes->provider_user_last_name . ' ' . Emoji::RIGHT . ' ' : ''; ?><?= $user->provider_user_last_name ?><br/>
<?php if ($globalUser = $user->globalUser) : ?>
<br/>
<b>OSW ID</b>: #<?= $globalUser->getIdFullLink() ?><?= ($globalUser->username ? ' @' . $globalUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalUser->getRank() ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $globalUser->getRealConfirmations() ?><br/>
<?php endif; ?>
