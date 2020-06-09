<b>💰 <?= $sectionName ?></b><br/>
<br/>
<?= $adOffer->title ?><br/>
<br/>
<?php if ($keywords != '') : ?>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<?php endif; ?>
<?php if ($adOffer->description !== null) : ?>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adOffer->description ?><br/>
<?php endif; ?>
<?php if (isset($adOffer->price) && isset($currency)) : ?>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $adOffer->price ?> <?= $currency->code ?><br/>
<?php endif; ?>
<br/>
<?php if ($user->provider_user_name) : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> @<?= $user->provider_user_name ?>
<?php else : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <a href = "tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
<?php endif; ?>
