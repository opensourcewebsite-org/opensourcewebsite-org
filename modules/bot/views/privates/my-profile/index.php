
<b><?= Yii::t('bot', 'Your Profile') ?></b><br/>
<br/>
<?php if (isset($firstName)) : ?>
<?= Yii::t('bot', 'First Name') . ': ' . $firstName; ?><br/>
<?php endif; ?>
<?php if (isset($lastName)) : ?>
<?= Yii::t('bot', 'Last Name') . ': ' . $lastName; ?><br/>
<?php endif; ?>
<?php if (isset($username)) : ?>
<?= Yii::t('bot', 'Telegram') . ': @' . $username; ?><br/>
<?php endif; ?>
<br/>
<?php if (isset($gender)) : ?>
<?= Yii::t('bot', 'Gender') . ': ' . Yii::t('bot', $gender); ?><br/>
<?php endif; ?>
<?php if (isset($sexuality)) : ?>
<?= Yii::t('bot', 'Sexuality') . ': ' . Yii::t('bot', $sexuality); ?><br/>
<?php endif; ?>
<?php if (isset($birthday)) : ?>
<?= Yii::t('bot', 'Birthday') . ': ' . $birthday; ?><br/>
<?php endif; ?>
<?php if (!empty($languages)) : ?>
<br/>
<?=  Yii::t('bot', 'Languages') ?>:<br/>
<br/>
<?php foreach ($languages as $language) : ?>
<?= $language ?><br/>
<?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($citizenships)) : ?>
<br/>
<?= Yii::t('bot', 'Citizenships') ?>:<br/>
<br/>
<?php foreach ($citizenships as $citizenship) : ?>
<?= $citizenship ?><br/>
<?php endforeach; ?>
<?php endif; ?>
<br/>
<?php if ($location_lat && $location_lon) : ?>
<?= Yii::t('bot', 'Location') ?>: <a href = "<?= $locationLink ?>"><?= $location_lat ?> <?= $location_lon ?></a><br/>
<?php endif; ?>
<?php if (isset($timezone)) : ?>
<?= Yii::t('bot', 'Timezone') . ': ' . $timezone; ?><br/>
<?php endif; ?>
<?php if (isset($currency)) : ?>
<?= Yii::t('bot', 'Currency') . ': ' .  $currency; ?><br/>
<?php endif; ?>
