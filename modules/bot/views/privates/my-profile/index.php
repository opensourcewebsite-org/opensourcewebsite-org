<?php

use app\components\helpers\TimeHelper;
use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= Yii::t('bot', 'Your Profile') ?> #<?= $user->id ?></b><br/>
<br/>
<?php if ($telegramUser->provider_user_first_name) : ?>
<?= Yii::t('bot', 'First Name') . ': ' . $telegramUser->provider_user_first_name; ?><br/>
<?php endif; ?>
<?php if ($telegramUser->provider_user_last_name) : ?>
<?= Yii::t('bot', 'Last Name') . ': ' . $telegramUser->provider_user_last_name; ?><br/>
<?php endif; ?>
<?php if ($telegramUser->provider_user_name) : ?>
<?= Yii::t('bot', 'Telegram') . ': @' . $telegramUser->provider_user_name; ?><br/>
<?php endif; ?>
<br/>
<?php if ($user->birthday) : ?>
<?= Yii::t('bot', 'Birthday') . ': ' . $user->birthday; ?><br/>
<?php endif; ?>
<?php if ($user->gender) : ?>
<?= Yii::t('bot', 'Gender') . ': ' . Yii::t('bot', $user->gender->name); ?><br/>
<?php endif; ?>
<?php if ($user->sexuality) : ?>
<?= Yii::t('bot', 'Sexuality') . ': ' . Yii::t('bot', $user->sexuality->name); ?><br/>
<?php endif; ?>
<?php if ($user->languages) : ?>
<br/>
<?=  Yii::t('bot', 'Languages') ?>:<br/>
<?php foreach ($user->languages as $language) : ?>
  • <?= $language->getLabel(); ?><br/>
<?php endforeach; ?>
<?php endif; ?>
<?php if ($user->citizenships) : ?>
<br/>
<?= Yii::t('bot', 'Citizenships') ?>:<br/>
<?php foreach ($user->citizenships as $citizenship) : ?>
  • <?= Yii::t('user', $citizenship->country->name); ?><br/>
<?php endforeach; ?>
<?php endif; ?>
<br/>
<?php if ($userLocation = $telegramUser->userLocation) : ?>
<?= Yii::t('bot', 'Location') ?>: <a href="<?= ExternalLink::getOSMLink($userLocation->location_lat, $userLocation->location_lon) ?>"><?= $userLocation->location ?></a><br/>
<?php endif; ?>
<?php if ($user->timezone) : ?>
<?= Yii::t('bot', 'Timezone') . ': ' . TimeHelper::getNameByOffset($user->timezone); ?><br/>
<?php endif; ?>
<?php if ($user->currency) : ?>
<?= Yii::t('bot', 'Currency') . ': ' .  $user->currency->code . ' - ' . $user->currency->name; ?><br/>
<?php endif; ?>
