<b>🔍 <?= Yii::t('bot', $sectionName) ?></b><br/>
<br/>
<?= $adSearch->title ?><br/>
<br/>
<?php if ($adSearch->description !== null) : ?>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adSearch->description ?><br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= Yii::t('bot', $keywords) ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adSearch->location_lat ?> <?= $adSearch->location_lon ?></a><br/>
<?php if ($adSearch->pickup_radius > 0) : ?>
<b><?= Yii::t('bot', 'Pickup radius') ?>:</b> <?= $adSearch->pickup_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<?php endif; ?>
<br/>
<?php if ($user->provider_user_name) : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> @<?= $user->provider_user_name ?>
<?php else : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <a href = "tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
<?php endif; ?>
