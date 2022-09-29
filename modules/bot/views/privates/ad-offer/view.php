<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

?>
<?= Emoji::AD_OFFER ?> <b><?= Yii::t('bot', $model->getSectionName()) ?>: #<?= $model->id ?> <?= $model->title ?></b><br/>
<?php if ($keywords = $model->getKeywordsAsArray()) : ?>
<br/>
<i>#<?= implode(' #', $keywords); ?></i><br/>
<?php endif; ?>
<?php if ($model->description) : ?>
<br/>
<?= nl2br($model->description); ?><br/>
<?php endif; ?>
<?php if ($model->price) : ?>
<br/>
<b><?= Yii::t('bot', 'Price') ?></b>: <?= $model->price ?> <?= $model->currency->code ?><br/>
<?php endif; ?>
<br/>
<?= Emoji::HIDDEN ?> <i><?= Yii::t('bot', 'Location') ?>: <?= ExternalLink::getOSMFullLink($model->location_lat, $model->location_lon) ?></i><br/>
<?php if ($model->delivery_radius > 0) : ?>
<?= Emoji::HIDDEN ?> <i><?= Yii::t('bot', 'Delivery radius') ?>: <?= $model->delivery_radius ?> <?= Yii::t('bot', 'km') ?></i><br/>
<?php endif; ?>
————<br/>
<?= Emoji::HIDDEN ?> - <i><?= Yii::t('bot', 'Only you see this information') ?></i>.<br/>
<?php if ($model->isActive()) : ?>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?></i>.
<?php endif; ?>
