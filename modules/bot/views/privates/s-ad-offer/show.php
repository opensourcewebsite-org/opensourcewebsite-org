💰 <b><?= $sectionName ?></b> - <b><?= $adOffer->title ?></b><br/>
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
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adOffer->location_lat ?> <?= $adOffer->location_lon ?></a><br/>
<br/>
<?php if ($adOffer->delivery_radius > 0) : ?>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $adOffer->delivery_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<?php endif; ?>
<?php if ($adOffer->isActive() && $showDetailedInfo) : ?>
————<br/>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>.</i>
<?php endif; ?>
