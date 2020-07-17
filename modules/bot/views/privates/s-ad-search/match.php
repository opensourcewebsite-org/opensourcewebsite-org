<b>💰 <?= $sectionName ?></b> - <b><?= $adOffer->title ?></b><br/>
<br/>
<?php if ($adOffer->description !== null) : ?>
<?= nl2br($adOffer->description); ?><br/>
<br/>
<?php endif; ?>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if (isset($adOffer->price) && isset($currency)) : ?>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $adOffer->price ?> <?= $currency->code ?><br/>
<br/>
<?php endif; ?>
<?php if ($user) : ?>
<?php if ($user->provider_user_name) : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> @<?= $user->provider_user_name ?>
<?php else : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <a href = "tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
<?php endif; ?>
<?php endif; ?>
