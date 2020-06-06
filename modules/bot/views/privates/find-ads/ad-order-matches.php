<b>💰 <?= $categoryName ?></b><br/>
<br/>
<?= $adOrder->title ?><br/>
<br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adOrder->description ?><br/>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $adOrder->price / 100.0 ?> <?= $currency->code ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adOrder->location_latitude ?> <?= $adOrder->location_longitude ?></a><br/>
<?php if ($adOrder->delivery_radius > 0) : ?>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $adOrder->delivery_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<?php endif; ?>
<br/>
<?php if ($user->provider_user_name) : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> @<?= $user->provider_user_name ?>
<?php else : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <a href = "tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
<?php endif; ?>
