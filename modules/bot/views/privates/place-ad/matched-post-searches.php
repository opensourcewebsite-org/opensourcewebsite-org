<b>🔍 <?= Yii::t('bot', $categoryName) ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= Yii::t('bot', $keywords) ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adsPostSearch->location_latitude ?> <?= $adsPostSearch->location_longitude ?></a><br/>
<b><?= Yii::t('bot', 'Pickup radius') ?>:</b> <?= $adsPostSearch->radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<?php if ($user->provider_user_name) : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> @<?= $user->provider_user_name ?>
<?php else : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <a href = "tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
<?php endif; ?>
